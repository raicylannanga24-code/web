<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

$stud_id   = (int)$_GET['id'];
$school_id = (int)$_GET['school_id'];
$dept_id   = (int)$_GET['dept_id'];
$prog_id   = (int)$_GET['prog_id'];

// Fetch the student to display
$stmt = $pdo->prepare("SELECT * FROM students WHERE studid = ?");
$stmt->execute([$stud_id]);
$stud = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stud) {
    die("Student not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delete Student</title>
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
        <div class="topbar-title">Delete Student</div>
    </div>

    <div class="panel">
        <div class="panel-body">

            <p>You are about to delete the following student entry:</p>
            <br>

            <table style="width:60%; border-collapse:collapse; margin-bottom:20px;">
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">Student ID</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($stud['studid']) ?></td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">Last Name</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($stud['studlastname']) ?></td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">First Name</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($stud['studfirstname']) ?></td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">Middle Name</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($stud['studmidname']) ?></td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">Year Level</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($stud['studyear']) ?></td>
                </tr>
            </table>

            <a href="students.php?school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>&prog_id=<?= $prog_id ?>&action=select_prog"
               class="btn btn-secondary">Cancel Operation</a>

            <a href="confirmDeleteStudent.php?id=<?= $stud_id ?>&school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>&prog_id=<?= $prog_id ?>"
               class="btn btn-danger">Proceed</a>

        </div>
    </div>

</div>

</body>
</html>