<?php
session_start();

/* PROTECT DASHBOARD (no login = back to login page) */
if (!isset($_SESSION['username'])) {
    header("Location: mylogin.php");
    exit();
}

echo $_SESSION['userrole'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - USJR School Management System</title>
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>

<!-- TOP BAR -->
<div class="rectangle1">

    <p class="namePage">USJR-SCHOOL MANAGEMENT SYSTEM</p>

    <!-- NAVIGATION MENU -->
    <div class="navMenu">
            <a href="school/schools.php">Schools</a>
            <a href="department/department.php">Departments</a>
            <a href="programs/programs.php">Programs</a>
            <a href="student/students.php">Students</a>
            <a href="users/users.php">Users</a>
            <a href="logout.php">Logout</a>
    </div>

</div>

<!-- WELCOME SECTION -->
<div class="rectangle2">
    <p class="welcome">
        Welcome, <?php echo $_SESSION['username']; ?>
    </p>
    <p class="manage">Manage your school's operations efficiently</p>
</div>

<h2 class="Qaccess">Quick Access</h2>

<!-- MAIN CONTAINER -->
<div class="container">

    <!-- LEFT SIDE CARDS -->
    <div class="leftSide">
        <div class="cards">

            <a href="schools.php" class="card-btn">
                <div>🏫</div>
                <h4>Schools</h4>
                <p>Manage school information</p>
            </a>

            <a href="../department.php" class="card-btn">
                <div>📊</div>
                <h4>Departments</h4>
                <p>Organize departments</p>
            </a>

            <a href="programs.html" class="card-btn">
                <div>🎓</div>
                <h4>Programs</h4>
                <p>Manage programs</p>
            </a>

            <a href="students.html" class="card-btn">
                <div>👥</div>
                <h4>Students</h4>
                <p>Manage students</p>
            </a>

        </div>
    </div>

    <!-- CENTER LINE -->
    <div class="rectangle3"></div>

    <!-- RIGHT SIDE -->
    <div class="rightSide">
        <div class="getting">
            <b>Getting Started</b>
            <ol>
                <li>Use the navigation menu above</li>
                <li>Select a module (Schools, Students, etc.)</li>
                <li>Manage records</li>
                <li>Logout when done</li>
            </ol>
        </div>
    </div>

</div>

</body>
</html>