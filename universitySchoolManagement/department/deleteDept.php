<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

$dept_id   = (int)$_GET['id'];
$school_id = (int)$_GET['school_id'];

// Fetch the department to display
$stmt = $pdo->prepare("SELECT * FROM departments WHERE deptid = ?");
$stmt->execute([$dept_id]);
$dept = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dept) {
    die("Department not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delete Department</title>
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
        <div class="topbar-title">Delete Department</div>
    </div>

    <div class="panel">
        <div class="panel-body">

            <p>You are about to delete the following department entry:</p>
            <br>

            <table style="width:60%; border-collapse:collapse; margin-bottom:20px;">
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">Department ID</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= $dept['deptid'] ?></td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">Full Name</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($dept['deptfullname']) ?></td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">Short Name</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($dept['deptshortname']) ?></td>
                </tr>
            </table>

            <a href="department.php?school_id=<?= $school_id ?>" class="btn btn-secondary">Cancel Operation</a>
            <a href="confirmDeleteDept.php?id=<?= $dept['deptid'] ?>&school_id=<?= $school_id ?>" class="btn btn-danger">Proceed</a>

        </div>
    </div>

</div>

</body>
</html>