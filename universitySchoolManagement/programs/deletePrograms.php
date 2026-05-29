<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

$prog_id   = (int)$_GET['id'];
$school_id = (int)$_GET['school_id'];
$dept_id   = (int)$_GET['dept_id'];

// Fetch the program details
$stmt = $pdo->prepare("SELECT * FROM programs WHERE progid = ?");
$stmt->execute([$prog_id]);
$prog = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prog) {
    die("Program not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delete Program</title>
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
        <div class="topbar-title">Delete Program</div>
    </div>

    <div class="panel">
        <div class="panel-body">

            <p>You are about to delete the following program. Do you want to proceed?</p>
            <br>

            <table style="width:60%; border-collapse:collapse; margin-bottom:20px;">
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">Program ID</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= $prog['progid'] ?></td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">Full Name</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($prog['progfullname']) ?></td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">Short Name</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($prog['progshortname']) ?></td>
                </tr>
            </table>

            <p>This entry may be related to other data in the database. Deleting it may affect related records.</p>
            <br>

            <!-- Cancel goes back to programs list -->
            <a href="programs.php?school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>"
               class="btn btn-secondary">Cancel Operation</a>

            <!-- Proceed goes to confirmDeletePrograms.php -->
            <a href="confirmDeletePrograms.php?id=<?= $prog_id ?>&school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>"
               class="btn btn-danger">Proceed</a>

        </div>
    </div>

</div>

</body>
</html>