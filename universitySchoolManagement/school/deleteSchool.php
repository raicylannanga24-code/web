<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

$id = (int)$_GET['id'];

// fetch the school to display
$stmt = $pdo->prepare("SELECT * FROM colleges WHERE collid = ?");
$stmt->execute([$id]);
$school = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$school) {
    die("School not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delete School</title>
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
        <div class="topbar-title">Delete School</div>
    </div>

    <div class="panel">
        <div class="panel-body">

            <p>You are about to delete the following school entry:</p>
            <br>

            <table style="width:60%; border-collapse:collapse; margin-bottom:20px;">
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">School ID</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= $school['collid'] ?></td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">Full Name</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($school['collfullname']) ?></td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">Short Name</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($school['collshortname']) ?></td>
                </tr>
            </table>

            <a href="schools.php" class="btn btn-secondary">Cancel Operation</a>
            <a href="confirmDeleteSchool.php?id=<?= $school['collid'] ?>" class="btn btn-danger">Proceed</a>

        </div>
    </div>

</div>

</body>
</html>