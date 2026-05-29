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

$deleted = false;

// If user clicked Yes, Delete
if (isset($_POST['confirm_delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE userid = ?");
    $stmt->execute([$user_id]);
    $deleted = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Confirm Delete User</title>
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
        <div class="topbar-title">Confirm Delete User</div>
    </div>

    <div class="panel">
        <div class="panel-body">

        <?php if ($deleted): ?>

            <!-- SUCCESS — shown after deletion -->
            <div class="alert alert-success">User deleted successfully.</div>

            <table style="width:60%; border-collapse:collapse; margin-bottom:20px;">
                <tr>
                    <td style="padding:10px; border:1px solid #ccc; font-weight:bold; background:#fafafa;">User ID:</td>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($user['userid']) ?></td>
                </tr>
                <tr>
                    <td style="padding:10px; border:1px solid #ccc; font-weight:bold; background:#fafafa;">Username:</td>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($user['username']) ?></td>
                </tr>
                <tr>
                    <td style="padding:10px; border:1px solid #ccc; font-weight:bold; background:#fafafa;">User Type:</td>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($user['usertype']) ?></td>
                </tr>
                <tr>
                    <td style="padding:10px; border:1px solid #ccc; font-weight:bold; background:#fafafa;">User Role:</td>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($user['userrole']) ?></td>
                </tr>
            </table>

            <a href="manageUsers.php" class="btn btn-secondary">Back to User List</a>

        <?php else: ?>

            <!-- FINAL CONFIRMATION — Yes or No -->
            <p>Are you sure you want to permanently delete this user?</p>
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

            <form method="POST" action="confirmDeleteUser.php?id=<?= $user_id ?>">
                <button type="submit" name="confirm_delete" class="btn btn-danger">Yes, Delete</button>
                <a href="manageUsers.php" class="btn btn-secondary">No, Cancel</a>
            </form>

        <?php endif; ?>

        </div>
    </div>

</div>

</body>
</html>