<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

$stud_id   = isset($_GET['id'])        ? intval($_GET['id'])        : 0;
$school_id = isset($_GET['school_id']) ? intval($_GET['school_id']) : 0;
$dept_id   = isset($_GET['dept_id'])   ? intval($_GET['dept_id'])   : 0;
$prog_id   = isset($_GET['prog_id'])   ? intval($_GET['prog_id'])   : 0;
$errors = [];

// Fetch existing student data to pre-fill the form
$stmt = $pdo->prepare("SELECT * FROM students WHERE studid = ?");
$stmt->execute([$stud_id]);
$stud = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stud) {
    echo "Student not found.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stud_id   = intval($_POST['stud_id']);
    $school_id = intval($_POST['school_id']);
    $dept_id   = intval($_POST['dept_id']);
    $prog_id   = intval($_POST['prog_id']);
    $lastname  = trim($_POST['studlastname']);
    $firstname = trim($_POST['studfirstname']);
    $midname   = trim($_POST['studmidname']);
    $year      = trim($_POST['studyear']);

    if (empty($lastname))  $errors[] = "Last Name is required.";
    if (empty($firstname)) $errors[] = "First Name is required.";
    if ($year === '')      $errors[] = "Year Level is required.";

    if (empty($errors)) {
    if (
        $lastname  == $stud['studlastname'] &&
        $firstname == $stud['studfirstname'] &&
        $midname   == $stud['studmidname'] &&
        $year      == $stud['studyear']
    ) {
        $errors[] = "Nothing to update. No changes were made.";
    }
}

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE students
            SET studlastname = ?, studfirstname = ?, studmidname = ?, studyear = ?
            WHERE studid = ?
        ");
        if ($stmt->execute([$lastname, $firstname, $midname, $year, $stud_id])) {
            header("Location: students.php?school_id=$school_id&dept_id=$dept_id&prog_id=$prog_id&action=select_prog");
            exit();
        } else {
            $errors[] = "Error updating student.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
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
        <div class="topbar-title">Edit Student</div>
    </div>

    <?php if (!empty($errors)): ?>
        <ul style="color:red; margin-bottom:15px;">
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div class="form-card">

        <!-- BACK BUTTON -->
        <div style="margin-bottom: 16px;">
            <a href="students.php?school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>&prog_id=<?= $prog_id ?>&action=select_prog"
               class="btn btn-danger">⬅ Back</a>
        </div>

        <form action="editStudents.php?id=<?= $stud_id ?>&school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>&prog_id=<?= $prog_id ?>"
              method="POST">

            <input type="hidden" name="stud_id"   value="<?= $stud_id ?>">
            <input type="hidden" name="school_id" value="<?= $school_id ?>">
            <input type="hidden" name="dept_id"   value="<?= $dept_id ?>">
            <input type="hidden" name="prog_id"   value="<?= $prog_id ?>">

            <div class="form-group">
                <label>Student ID</label>
                <input type="number" value="<?= htmlspecialchars($stud['studid']) ?>" disabled>
            </div>

            <div class="form-group">
                <label>Last Name <span style="color:red;">*</span></label>
                <input type="text" name="studlastname"
                       value="<?= htmlspecialchars($_POST['studlastname'] ?? $stud['studlastname']) ?>"
                       placeholder="e.g. Dela Cruz" required>
            </div>

            <div class="form-group">
                <label>First Name <span style="color:red;">*</span></label>
                <input type="text" name="studfirstname"
                       value="<?= htmlspecialchars($_POST['studfirstname'] ?? $stud['studfirstname']) ?>"
                       placeholder="e.g. Juan" required>
            </div>

            <div class="form-group">
                <label>Middle Name <em style="font-weight:normal; color:#888;">(optional)</em></label>
                <input type="text" name="studmidname"
                       value="<?= htmlspecialchars($_POST['studmidname'] ?? $stud['studmidname']) ?>"
                       placeholder="e.g. Santos">
            </div>

            <div class="form-group">
                <label>Year Level <span style="color:red;">*</span></label>
                <input type="text" name="studyear" min="1" max="4"
                       value="<?= htmlspecialchars($_POST['studyear'] ?? $stud['studyear']) ?>"
                       placeholder="Enter year (1-4)" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Update Student</button>
                <a href="students.php?school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>&prog_id=<?= $prog_id ?>&action=select_prog"
                   class="btn btn-danger">✖ Cancel</a>
            </div>

        </form>
    </div>

</div>

</body>
</html>