<?php
session_start();
if (!isset($_SESSION['username'])) { header("Location: ../mylogin.php"); exit(); }
if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'Administrator') {
    header("Location: ../mydashboard.php");
    exit();
}

require '../database.php';

$msg      = '';
$msg_type = '';
$results  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvfile'])) {
    $file = $_FILES['csvfile'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $msg      = "File upload error.";
        $msg_type = "danger";
    } elseif (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'csv') {
        $msg      = "Only CSV files are allowed.";
        $msg_type = "danger";
    } else {
        $handle = fopen($file['tmp_name'], 'r');
       $header = fgetcsv($handle, 0, ',', '"', '\\');// skip header row
       
        $success = 0;
        $failed  = 0;

       while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (count($row) < 4) { $failed++; continue; }

            $username = trim($row[0]);
            $password = trim($row[1]);
            $usertype = trim($row[2]);
            $userrole = trim($row[3]);

            // Normalize values
            $usertype = ($usertype === 'Administrator') ? 'Administrator' : 'User';

            $valid_roles = ['Administrator', 'Viewer', 'Updater', 'Remover', 'Creator'];
            if (!in_array($userrole, $valid_roles)) {
                $failed++;
                $results[] = ['username' => $username, 'status' => 'failed', 'reason' => 'Invalid role'];
                continue;
            }

            // Set system role 
            $role = ($usertype === 'Administrator') ? 'admin' : 'staff';

            if (!$username) { $failed++; continue; }

            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, password, usertype, userrole) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $password, $usertype, $userrole]);

                $success++;
                $results[] = ['username' => $username, 'status' => 'success'];
            } catch (PDOException $e) {
                $failed++;
                $results[] = ['username' => $username, 'status' => 'failed', 'reason' => 'Username already exists'];
            }
        }

        fclose($handle);

        $msg      = "{$success} user(s) added, {$failed} failed.";
        $msg_type = $failed > 0 ? "warning" : "success";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload Users — USJR</title>
    <link rel="stylesheet" href="../schools.css">
</head>
<body>

    <div class="rectangle1">
    <p class="namePage">USJR-SCHOOL MANAGEMENT SYSTEM</p>
    <div class="navMenu">
        <a href="../mydashboard.php">Home</a>
        <a href="../school/schools.php">Schools</a>
        <a href="../department/department.php">Departments</a>
        <a href="../programs/programs.php">Programs</a>
        <a href="../student/students.php">Students</a>
        <a href="../users/users.php">Users</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="layout">

    <div class="main-content">

        <div class="topbar">
            <h3>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h3>
            <div class="topbar-badge"><?= htmlspecialchars($_SESSION['username']) ?></div>
        </div>

        <div class="page-body">
            <div class="panel">

                <div class="panel-header">
                    <span class="panel-title">Add Users From File</span>
                </div>

                <?php if ($msg): ?>
                <div class="alert alert-<?= $msg_type ?>">
                    <?= $msg_type === 'warning' ? '' : ($msg_type === 'success' ? '✅' : '❌') ?> <?= htmlspecialchars($msg) ?>
                </div>
                <?php endif; ?>

                <div class="panel-body">

                    <div class="alert alert-info" style="margin-bottom: 20px;">
                       CSV format: <strong>username, password, usertype, userrole</strong> (role must be <code>admin</code> or <code>staff</code>). First row is treated as header and skipped.
                    </div>

                    <form method="POST" enctype="multipart/form-data" action="addUser.php">
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label>Select CSV File</label>
                            <input type="file" name="csvfile" accept=".csv" required>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Upload</button>
                            <a href="users.php" class="btn btn-ghost">Exit</a>
                        </div>
                    </form>

                    <?php if (!empty($results)): ?>
                    <div style="margin-top: 30px;">
                        <div class="panel-title" style="margin-bottom: 12px;">Upload Results</div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Status</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $r): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['username']) ?></td>
                                        <td>
                                            <?php if ($r['status'] === 'success'): ?>
                                                <span class="badge badge-green">Added</span>
                                            <?php else: ?>
                                                <span class="badge badge-red">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($r['reason'] ?? '—') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</div>
</body>
</html>