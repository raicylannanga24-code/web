<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

$school_id = isset($_GET['school_id']) ? intval($_GET['school_id']) : 0;
$dept_id   = isset($_GET['dept_id'])   ? intval($_GET['dept_id'])   : 0;
$prog_id   = isset($_GET['prog_id'])   ? intval($_GET['prog_id'])   : 0;

// Fetch school name
$school_name = '';
if ($school_id > 0) {
    $stmt = $pdo->prepare("SELECT collfullname FROM colleges WHERE collid = ?");
    $stmt->execute([$school_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $school_name = $row ? $row['collfullname'] : '';
}

// Fetch department name
$dept_name = '';
if ($dept_id > 0) {
    $stmt = $pdo->prepare("SELECT deptfullname FROM departments WHERE deptid = ?");
    $stmt->execute([$dept_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $dept_name = $row ? $row['deptfullname'] : '';
}

// Fetch program name
$prog_name = '';
if ($prog_id > 0) {
    $stmt = $pdo->prepare("SELECT progfullname FROM programs WHERE progid = ?");
    $stmt->execute([$prog_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $prog_name = $row ? $row['progfullname'] : '';
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studid    = trim($_POST['studid']    ?? '');
    $lastname  = trim($_POST['lastname']  ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $midname   = trim($_POST['midname']   ?? '');
    $year      = trim($_POST['year']      ?? '');

    if ($studid === '' || $lastname === '' || $firstname === '' || $year === '') {
        $error = 'Student ID, Last Name, First Name, and Year Level are required.';
    } elseif (!ctype_digit($studid)) {
        $error = 'Student ID must contain numbers only.';
    } elseif (intval($studid) > 2147483647) {
        $error = 'Student ID is too large. Maximum value is 2,147,483,647.';
    } else {
        // Check if studid already exists
        $check = $pdo->prepare("SELECT studid FROM students WHERE studid = ?");
        $check->execute([$studid]);
        if ($check->fetch()) {
            $error = 'Student ID already exists. Please use a different ID.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO students (studid, studfirstname, studlastname, studmidname, studyear, studcollid, studcolldeptid, studprogid)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([intval($studid), $firstname, $lastname, $midname, $year, $school_id, $dept_id, $prog_id]);
            header("Location: students.php?school_id=$school_id&dept_id=$dept_id&prog_id=$prog_id&action=select_prog");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
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
        <div class="topbar-title">Add Student</div>
    </div>

    <div class="form-card">

        <!-- BACK BUTTON -->
        <div style="margin-bottom: 16px;">
            <a href="students.php?school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>&prog_id=<?= $prog_id ?>&action=select_prog"
               class="btn btn-danger">⬅ Back</a>
        </div>

        <!-- Context: School / Dept / Program -->
        <div class="context-info">
            <strong><?= htmlspecialchars($school_name) ?></strong>
            <strong><?= htmlspecialchars($dept_name) ?></strong>
            <strong><?= htmlspecialchars($prog_name) ?></strong>
        </div>

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST"
              action="addStudents.php?school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>&prog_id=<?= $prog_id ?>">

            <div class="form-group">
                <label>Student ID <span>*</span></label>
                <input type="number" name="studid"
                        onkeydown="return !['e','E','+','-','.'].includes(event.key)"
                       value="<?= htmlspecialchars($_POST['studid'] ?? '') ?>"
                       placeholder="e.g. 20240000000">
            </div>

            <div class="form-group">
                <label>Last Name <span>*</span></label>
                <input type="text" name="lastname"
                       value="<?= htmlspecialchars($_POST['lastname'] ?? '') ?>"
                       placeholder="e.g. Dela Cruz">
            </div>

            <div class="form-group">
                <label>First Name <span>*</span></label>
                <input type="text" name="firstname"
                       value="<?= htmlspecialchars($_POST['firstname'] ?? '') ?>"
                       placeholder="e.g. Juan">
            </div>

            <div class="form-group">
                <label>Middle Name <em style="font-weight:normal; color:#888;">(optional)</em></label>
                <input type="text" name="midname"
                       value="<?= htmlspecialchars($_POST['midname'] ?? '') ?>"
                       placeholder="e.g. Santos">
            </div>

            <div class="form-group">
                <label>Year Level <span>*</span></label>
                <input type="text" name="year" min="1" max="4"
                       value="<?= htmlspecialchars($_POST['year'] ?? '') ?>"
                       placeholder="Enter year (1-4)">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Save Student</button>
                <a href="students.php?school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>&prog_id=<?= $prog_id ?>&action=select_prog"
                   class="btn btn-danger">✖ Cancel</a>
            </div>

        </form>
    </div>

</div>

</body>
</html>