<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

// Fetch all schools for the dropdown
$schools = [];
$res = $pdo->query("SELECT collid, collfullname FROM colleges ORDER BY collfullname");
$schools = $res->fetchAll(PDO::FETCH_ASSOC);

// Get selected school from GET param
$selected_school = isset($_GET['school_id']) ? intval($_GET['school_id']) : 0;

// Search query
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Fetch departments for selected school (with optional search)
$departments = [];
$total = 0;
if ($selected_school > 0) {
    if ($search) {
        $stmt = $pdo->prepare("
            SELECT deptid, deptfullname, deptshortname
            FROM departments
            WHERE deptcollid = ?
              AND (deptfullname LIKE ? OR deptshortname LIKE ?)
        ");
        $stmt->execute([$selected_school, "%$search%", "%$search%"]);
    } else {
        $stmt = $pdo->prepare("
            SELECT deptid, deptfullname, deptshortname
            FROM departments
            WHERE deptcollid = ?
        ");
        $stmt->execute([$selected_school]);
    }
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Total count (unfiltered)
    $countStmt = $pdo->prepare("SELECT COUNT(*) AS c FROM departments WHERE deptcollid = ?");
    $countStmt->execute([$selected_school]);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['c'];
}

// Flash message support
$msg      = isset($_SESSION['msg'])      ? $_SESSION['msg']      : '';
$msg_type = isset($_SESSION['msg_type']) ? $_SESSION['msg_type'] : '';
unset($_SESSION['msg'], $_SESSION['msg_type']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Departments</title>
    <link rel="stylesheet" href="../schools.css">

    <style>
    .school-selector select {
        padding: 8px 12px;
        font-size: 14px;
        width: 400px;
        border: 1px solid #ccc;
        border-radius: 5px;
        height: 36px;
        background: white;
    }
    .school-selector label {
        font-size: 14px;
        font-weight: bold;
    }
    </style>
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
        <div class="topbar-title">Departments</div>
        <?php if ($selected_school > 0): ?>
            <div class="topbar-badge"><?= $total ?> total</div>
        <?php endif; ?>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <!-- SCHOOL SELECTOR -->
    <form method="GET" action="department.php" class="school-selector"
          style="display:flex; align-items:center; gap:10px; margin-bottom:20px;">
        <label><strong>Select School:</strong></label>
        <select name="school_id">
            <option value="">-- Choose a School --</option>
            <?php foreach ($schools as $s): ?>
                <option value="<?= $s['collid'] ?>"
                    <?= $selected_school == $s['collid'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['collfullname']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Select School</button>
    </form>

    <?php if ($selected_school > 0): ?>

        <!-- ADD DEPARTMENT BUTTON -->
        <a href="addDept.php?school_id=<?= $selected_school ?>" class="btn btn-primary">
            ➕ Add Department
        </a>

        <br><br>

        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">All Departments</div>

                <!-- SEARCH BAR -->
                <form method="GET" action="department.php">
                    <input type="hidden" name="school_id" value="<?= $selected_school ?>">
                    <div class="search-input-wrap">
                        <input type="text" name="q"
                               placeholder="Search..."
                               value="<?= htmlspecialchars($search) ?>">
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
                    <?php if (count($departments) == 0): ?>
                        <tr>
                            <td colspan="4">
                                <?= $search
                                    ? "No departments found for \"" . htmlspecialchars($search) . "\"."
                                    : "No departments found for this school." ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($departments as $d): ?>
                        <tr>
                            <td><?= $d['deptid'] ?></td>
                            <td><?= htmlspecialchars($d['deptfullname']) ?></td>
                            <td>
                                <span class="badge badge-blue">
                                    <?= htmlspecialchars($d['deptshortname']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="editDept.php?id=<?= $d['deptid'] ?>&school_id=<?= $selected_school ?>"
                                   class="btn btn-warning btn-xs">Edit</a>

                                <a href="deleteDept.php?id=<?= $d['deptid'] ?>&school_id=<?= $selected_school ?>"
                                   class="btn btn-danger btn-xs">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <br>
        <p>Total of: <?= $total ?> department(s) in the database</p>

    <?php endif; ?>

</div>

</body>
</html>