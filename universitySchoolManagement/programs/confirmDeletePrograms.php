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

$deleted = false;

// If user clicked Yes, Delete
if (isset($_POST['confirm_delete'])) {
    $stmt = $pdo->prepare("DELETE FROM programs WHERE progid = ?");
    $stmt->execute([$prog_id]);
    $deleted = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Confirm Delete Program</title>
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
        <div class="topbar-title">Confirm Delete Program</div>
    </div>

    <div class="panel">
        <div class="panel-body">

        <?php if ($deleted): ?>

            <!-- SUCCESS — shown after deletion -->
            <div class="alert alert-success">Program record deleted successfully.</div>

            <table style="width:60%; border-collapse:collapse; margin-bottom:20px;">
                <tr>
                    <td style="padding:10px; border:1px solid #ccc; font-weight:bold; background:#fafafa;">Program ID:</td>
                    <td style="padding:10px; border:1px solid #ccc;"><?= $prog['progid'] ?></td>
                </tr>
                <tr>
                    <td style="padding:10px; border:1px solid #ccc; font-weight:bold; background:#fafafa;">Full Name:</td>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($prog['progfullname']) ?></td>
                </tr>
                <tr>
                    <td style="padding:10px; border:1px solid #ccc; font-weight:bold; background:#fafafa;">Short Name:</td>
                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($prog['progshortname']) ?></td>
                </tr>
            </table>

            <a href="programs.php?school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>"
               class="btn btn-secondary">Back to Program List</a>

        <?php else: ?>

            <!-- FINAL CONFIRMATION — Yes or No -->
            <p>Are you sure you want to permanently delete this program?</p>
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

            <form method="POST" action="confirmDeletePrograms.php?id=<?= $prog_id ?>&school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>">
                <button type="submit" name="confirm_delete" class="btn btn-danger">Yes, Delete</button>
                <a href="programs.php?school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>"
                   class="btn btn-secondary">No, Cancel</a>
            </form>

        <?php endif; ?>

        </div>
    </div>

</div>

</body>
</html>