<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

$user_id = (int)$_GET['id'];

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE userid = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delete User</title>
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
        <div class="topbar-title">Delete User</div>
    </div>

    <div class="panel">
        <div class="panel-body">

            <p>⚠️ You are about to delete the following user. Do you want to proceed?</p>
            <br>

            <table style="width:60%; border-collapse:collapse; margin-bottom:20px;">
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">User ID</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($user['userid']) ?></td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">Username</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($user['username']) ?></td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">User Type</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($user['usertype']) ?></td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:10px; border:1px solid #ccc; background:#fafafa;">User Role</th>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($user['userrole']) ?></td>
                </tr>
            </table>

            <p>This action may affect related data in the system.</p>
            <br>

            <!-- Cancel goes back to user list -->
            <a href="manageUsers.php" class="btn btn-secondary">Cancel Operation</a>

            <!-- Proceed goes to confirmDeleteUser.php -->
            <a href="confirmDeleteUser.php?id=<?= $user_id ?>" class="btn btn-danger">Proceed</a>

        </div>
    </div>

</div>

</body>
</html>