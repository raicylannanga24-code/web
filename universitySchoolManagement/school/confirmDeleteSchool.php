<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

$id = (int)$_GET['id'];

// fetch the school details
$stmt = $pdo->prepare("SELECT * FROM colleges WHERE collid = ?");
$stmt->execute([$id]);
$school = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$school) {
    die("School not found.");
}

$deleted = false;

// if user clicked Yes Delete
if (isset($_POST['confirm_delete'])) {
    $stmt = $pdo->prepare("DELETE FROM colleges WHERE collid = ?");
    $stmt->execute([$id]);
    $deleted = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Confirm Delete School</title>
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
        <div class="topbar-title">Confirm Delete School</div>
    </div>

    <div class="panel">
        <div class="panel-body">

        <?php if ($deleted): ?>

            <!-- SUCCESS PAGE — shown after deletion -->
            <div class="alert alert-success">School record deleted successfully.</div>

            <table style="width:60%; border-collapse:collapse; margin-bottom:20px;">
                <tr>
                    <td style="padding:10px; border:1px solid #ccc; font-weight:bold; background:#fafafa;">School ID:</td>
                    <td style="padding:10px; border:1px solid #ccc;"><?= $school['collid'] ?></td>
                </tr>
                <tr>
                    <td style="padding:10px; border:1px solid #ccc; font-weight:bold; background:#fafafa;">Full Name:</td>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($school['collfullname']) ?></td>
                </tr>
                <tr>
                    <td style="padding:10px; border:1px solid #ccc; font-weight:bold; background:#fafafa;">Short Name:</td>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($school['collshortname']) ?></td>
                </tr>
            </table>

            <a href="schools.php" class="btn btn-secondary">Back to School List</a>

        <?php else: ?>

            <!-- CONFIRMATION PAGE — shown before deletion -->
            <p>Are you sure you want to delete this school entry?</p>
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

            <p>This entry may be related to other data in the database. Deleting it may affect related records.</p>
            <br>

            <form method="POST">
                <button type="submit" name="confirm_delete" class="btn btn-danger">Yes, Delete Entry</button>
                <a href="schools.php" class="btn btn-secondary">No, Cancel</a>
            </form>

        <?php endif; ?>

        </div>
    </div>

</div>

</body>
</html>