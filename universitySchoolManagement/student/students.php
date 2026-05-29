<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

$clicked = isset($_GET['action']) ? $_GET['action'] : 'reset';

$selected_school = ($clicked !== 'reset' && isset($_GET['school_id'])) ? intval($_GET['school_id']) : 0;
$selected_dept   = ($clicked !== 'reset' && isset($_GET['dept_id']))   ? intval($_GET['dept_id'])   : 0;
$selected_prog   = ($clicked !== 'reset' && isset($_GET['prog_id']))   ? intval($_GET['prog_id'])   : 0;

// Fetch all schools
$schools = [];
$res = $pdo->query("SELECT collid, collfullname FROM colleges ORDER BY collfullname");
$schools = $res->fetchAll(PDO::FETCH_ASSOC);

// Load departments only after school button clicked
$departments = [];
if ($selected_school > 0 && $clicked !== 'reset') {
    $stmt = $pdo->prepare("SELECT deptid, deptfullname FROM departments WHERE deptcollid = ?");
    $stmt->execute([$selected_school]);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Load programs only after department button clicked
$programs = [];
if ($selected_dept > 0 && in_array($clicked, ['select_dept', 'select_prog'])) {
    $stmt = $pdo->prepare("SELECT progid, progfullname, progshortname FROM programs WHERE progcolldeptid = ?");
    $stmt->execute([$selected_dept]);
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch context names for display
$school_name = $dept_name = $prog_name = '';
if ($selected_school > 0) {
    $r = $pdo->prepare("SELECT collfullname FROM colleges WHERE collid = ?");
    $r->execute([$selected_school]);
    $row = $r->fetch(PDO::FETCH_ASSOC);
    $school_name = $row ? $row['collfullname'] : '';
}
if ($selected_dept > 0) {
    $r = $pdo->prepare("SELECT deptfullname FROM departments WHERE deptid = ?");
    $r->execute([$selected_dept]);
    $row = $r->fetch(PDO::FETCH_ASSOC);
    $dept_name = $row ? $row['deptfullname'] : '';
}
if ($selected_prog > 0) {
    $r = $pdo->prepare("SELECT progfullname FROM programs WHERE progid = ?");
    $r->execute([$selected_prog]);
    $row = $r->fetch(PDO::FETCH_ASSOC);
    $prog_name = $row ? $row['progfullname'] : '';
}

// Fetch students for the selected program
$students = [];
if ($clicked === 'select_prog' && $selected_prog > 0) {
    $stmt = $pdo->prepare("
        SELECT studid, studlastname, studfirstname, studmidname, studyear
        FROM students
        WHERE studprogid = ?
        ORDER BY studlastname, studfirstname
    ");
    $stmt->execute([$selected_prog]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Students</title>
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
</div>  <!-- ✅ FIXED: missing closing </div> for rectangle1 -->

<div class="main-content">

    <div class="topbar">
        <div class="topbar-title">Students</div>
    </div>

    <div class="filter-section">

        <!-- STEP 1: SELECT SCHOOL -->
        <form method="GET" action="students.php" style="display:contents;">
            <div class="dropdown-row">
                <label><strong>Select School:</strong></label>
                <select name="school_id">
                    <option value="">-- Choose a School --</option>
                    <?php foreach ($schools as $s): ?>
                        <option value="<?= $s['collid'] ?>"
                            <?= $selected_school == $s['collid'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['collfullname']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="action" value="select_school">
                <button type="submit" class="btn btn-primary">Select School</button>
            </div>
        </form>

        <!-- STEP 2: SELECT DEPARTMENT -->
        <form method="GET" action="students.php" style="display:contents;">
            <div class="dropdown-row">
                <input type="hidden" name="school_id" value="<?= $selected_school ?>">
                <input type="hidden" name="action" value="select_dept">
                <label><strong>Select Department:</strong></label>
                <select name="dept_id" <?= $selected_school == 0 ? 'disabled' : '' ?>>
                    <option value="">-- Choose a Department --</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['deptid'] ?>"
                            <?= $selected_dept == $d['deptid'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['deptfullname']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary"
                        <?= $selected_school == 0 ? 'disabled' : '' ?>>
                    Select Department
                </button>
            </div>
        </form>

        <!-- STEP 3: SELECT PROGRAM -->
        <form method="GET" action="students.php" style="display:contents;"
              onsubmit="return validateProgForm()">
            <div class="dropdown-row">
                <input type="hidden" name="school_id" value="<?= $selected_school ?>">
                <input type="hidden" name="dept_id" value="<?= $selected_dept ?>">
                <input type="hidden" name="action" value="select_prog">
                <label><strong>Select Program:</strong></label>
                <select name="prog_id" id="prog_select"
                        <?= ($selected_dept == 0 || in_array($clicked, ['reset', 'select_school'])) ? 'disabled' : '' ?>>
                    <option value="">-- Choose a Program --</option>
                    <?php foreach ($programs as $p): ?>
                        <option value="<?= $p['progid'] ?>"
                            <?= $selected_prog == $p['progid'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['progfullname']) ?>
                            (<?= htmlspecialchars($p['progshortname']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary"
                        <?= ($selected_dept == 0 || in_array($clicked, ['reset', 'select_school'])) ? 'disabled' : '' ?>>
                    Select Program
                </button>
            </div>
        </form>

    </div><!-- end filter-section -->

    <!-- STUDENT LIST TABLE — shown only after Select Program is clicked -->
    <?php if ($clicked === 'select_prog' && $selected_prog > 0): ?>

        <br>

        <!-- Context info -->
        <p style="margin:0; font-weight:700; font-size:0.95rem;"><?= htmlspecialchars($school_name) ?></p>
        <p style="margin:0; font-weight:700; font-size:0.95rem;"><?= htmlspecialchars($dept_name) ?></p>
        <p style="margin:0 0 14px 0; font-weight:700; font-size:0.95rem;"><?= htmlspecialchars($prog_name) ?></p>

        <!-- Add Student button -->
        <a href="addStudents.php?school_id=<?= $selected_school ?>&dept_id=<?= $selected_dept ?>&prog_id=<?= $selected_prog ?>"
           class="btn btn-primary" style="margin-bottom: 14px; display:inline-block;">
            ➕ Create Student Entry
        </a>

        <a href="students.php" class="btn btn-danger" style="margin-bottom: 14px; display:inline-block;">
            🔙 Back
        </a>

        <br><br>

        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">Student List</div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID #</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($students) === 0): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; color:#888;">
                                No students enrolled in this program yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $st): ?>
                        <tr>
                            <td><?= htmlspecialchars($st['studid']) ?></td>
                            <td><?= htmlspecialchars($st['studlastname']) ?></td>
                            <td><?= htmlspecialchars($st['studfirstname']) ?></td>
                            <td><?= htmlspecialchars($st['studmidname']) ?></td>
                            <td><?= htmlspecialchars($st['studyear']) ?></td>
                            <td>
                                <a href="editStudents.php?id=<?= $st['studid'] ?>&school_id=<?= $selected_school ?>&dept_id=<?= $selected_dept ?>&prog_id=<?= $selected_prog ?>"
                                   class="btn btn-warning btn-xs">Edit</a>
                                <a href="deleteStudent.php?id=<?= $st['studid'] ?>&school_id=<?= $selected_school ?>&dept_id=<?= $selected_dept ?>&prog_id=<?= $selected_prog ?>"
                                   class="btn btn-danger btn-xs">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <br>
        <p>Total of: <?= count($students) ?> student(s) in the database</p>

    <?php endif; ?>

</div><!-- end main-content -->

<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, '', 'students.php');
    }

    function validateProgForm() {
        const prog = document.getElementById('prog_select');
        if (!prog || prog.value === '') {
            alert('Please choose a program.');
            return false;
        }
        return true;
    }
</script>

</body>
</html>