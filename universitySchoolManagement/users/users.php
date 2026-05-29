<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Dashboard</title>
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
        <div class="topbar-title">User Dashboard</div>
    </div>

    <div style="text-align:center; margin-top:80px;">
        <p style="font-weight:bold; font-size:1.1rem;">User Dashboard</p>
        <p style="color:#555; margin-bottom:30px;">
            Welcome to the User Dashboard. Here you can manage user accounts and settings.
        </p>
        <div style="display:flex; flex-direction:column; align-items:center; gap:12px;">
            <a href="manageUsers.php" class="btn btn-secondary" style="width:200px; text-align:center;">
                Manage Users
            </a>
            <a href="addUser.php" class="btn btn-primary" style="width:200px; text-align:center;">
                Add Users
            </a>
        </div>
    </div>

</div>

</body>
</html>