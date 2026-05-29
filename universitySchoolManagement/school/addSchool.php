<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

$msg = '';
$msg_type = '';

if (isset($_POST['add_school'])) {
    $id    = (int)$_POST['collid'];
    $full  = trim($_POST['collfullname']);
    $short = trim($_POST['collshortname']);

    if (isset($_POST['add_school'])) {
    $id    = trim($_POST['collid']);
    $full  = trim($_POST['collfullname']);
    $short = trim($_POST['collshortname']);

    // Validate ID is numeric
    if (empty($id) || !is_numeric($id) || (int)$id <= 0) {
        $msg      = "School ID must be a positive number. Letters are not allowed.";
        $msg_type = "danger";
    } elseif (empty($full) || empty($short)) {
        $msg      = "All fields are required.";
        $msg_type = "danger";
    } else {
        $id = (int)$id;
        try {
            $stmt = $pdo->prepare("INSERT INTO colleges (collid, collfullname, collshortname) VALUES (?, ?, ?)");
            $r = $stmt->execute([$id, $full, $short]);

            if ($r) {
                header("Location: schools.php?added=1");
                exit();
            } else {
                $msg      = "Failed to add school.";
                $msg_type = "danger";
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $msg      = "School ID <strong>{$id}</strong> already exists. Please use a different ID.";
                $msg_type = "danger";
            } else {
                $msg      = "Database error: " . $e->getMessage();
                $msg_type = "danger";
            }
        }
    }
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add School</title>
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
        <div class="topbar-title">Add New School</div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?>">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">➕ Add School</div>
        </div>
        <div class="panel-body">
            <form method="POST">
                <div class="form-grid form-grid-3">
                    <div class="form-group">
                        <label>School ID</label>
                        <input type="number" name="collid" required
                            onkeydown="return !['e','E','+','-','.'].includes(event.key)"
                            value="<?= isset($_POST['collid']) ? (int)$_POST['collid'] : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="collfullname" required
                               value="<?= isset($_POST['collfullname']) ? htmlspecialchars($_POST['collfullname']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Short Name</label>
                        <input type="text" name="collshortname" required
                               value="<?= isset($_POST['collshortname']) ? htmlspecialchars($_POST['collshortname']) : '' ?>">
                    </div>
                </div>
                <br>
                <button type="submit" name="add_school" class="btn btn-primary">
                    Add School
                </button>
                <button type="reset" class="btn btn-warning">
                    Reset
                </button>
                <a href="schools.php" class="btn btn-secondary">
                    Cancel
                </a>
            </form>
        </div>
    </div>

</div>

</body>
</html>