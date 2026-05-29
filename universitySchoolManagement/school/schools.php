<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

$msg = '';
$msg_type = '';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM colleges WHERE collid = ?");
    $r = $stmt->execute([$id]);

    $msg      = $r ? "School deleted." : "Delete failed.";
    $msg_type = $r ? "success" : "danger";
}

$search = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM colleges WHERE collfullname LIKE ? OR collshortname LIKE ? ORDER BY collid");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM colleges ORDER BY collid");
}
$schools = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalStmt = $pdo->query("SELECT COUNT(*) AS c FROM colleges");
$total     = $totalStmt->fetch(PDO::FETCH_ASSOC)['c'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Schools</title>
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

<div class="main-content">

    <div class="topbar">
        <div class="topbar-title">Schools</div>
        <div class="topbar-badge"><?= $total ?> total</div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?>">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <!-- CHANGED: removed the Add School form, replaced with a simple button -->
    <a href="addSchool.php" class="btn btn-primary">➕ Add School</a>

    <br><br>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">All Schools</div>
            <form method="GET">
                <div class="search-input-wrap">
                     <input type="text" name="q" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Short Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($schools) == 0): ?>
                    <tr>
                        <td colspan="4">No schools found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($schools as $s): ?>
                    <tr>
                        <td><?= $s['collid'] ?></td>
                        <td><?= htmlspecialchars($s['collfullname']) ?></td>
                        <td>
                            <span class="badge badge-blue">
                                <?= htmlspecialchars($s['collshortname']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="editSchool.php?id=<?= $s['collid'] ?>"
                                class="btn btn-warning btn-xs">
                                Edit
                            </a>

                            <a href="deleteSchool.php?id=<?= $s['collid'] ?>"
                                class="btn btn-danger btn-xs">
                                Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>