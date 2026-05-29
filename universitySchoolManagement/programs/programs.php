<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

// Fetch all schools for first dropdown
$schools = [];
$res = $pdo->query("SELECT collid, collfullname FROM colleges ORDER BY collfullname");
$schools = $res->fetchAll(PDO::FETCH_ASSOC);

$selected_school = isset($_GET['school_id']) ? intval($_GET['school_id']) : 0;
$selected_dept   = isset($_GET['dept_id'])   ? intval($_GET['dept_id'])   : 0;

// Only load departments AFTER school button is clicked
// We know button was clicked if school_id is in the URL
$departments = [];
if ($selected_school > 0) {
    $stmt = $pdo->prepare("SELECT deptid, deptfullname FROM departments WHERE deptcollid = ?");
    $stmt->execute([$selected_school]);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch programs for selected department
$programs = [];
if ($selected_dept > 0) {
    $stmt = $pdo->prepare("SELECT progid, progfullname, progshortname FROM programs WHERE progcolldeptid = ?");
    $stmt->execute([$selected_dept]);
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Programs</title>
    <link rel="stylesheet" href="../schools.css">
    <style>
        select:disabled {
            background: #e9e9e9;
            color: #aaa;
            cursor: not-allowed;
        }
        button:disabled {
            background: #ccc;
            color: #888;
            cursor: not-allowed;
        }
    </style>
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
        <div class="topbar-title">Programs</div>
    </div>

    <!-- STEP 1: SELECT SCHOOL — only submits when button is clicked -->
    <form method="GET" style="display:flex; align-items:center; gap:10px; margin-bottom:15px;">
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
        <button type="submit" class="btn btn-primary">Select School</button>
    </form>

    <!-- STEP 2: SELECT DEPARTMENT — only enabled after school button clicked -->
    <form method="GET" style="display:flex; align-items:center; gap:10px; margin-bottom:20px;"
          onsubmit="return validateDeptForm()">

        <input type="hidden" name="school_id" value="<?= $selected_school ?>">

        <label><strong>Select Department:</strong></label>
        <select name="dept_id" id="dept_select"
                <?= $selected_school == 0 ? 'disabled' : '' ?>>
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
    </form>

    <!-- STEP 3: PROGRAMS TABLE — only after department is chosen -->
    <?php if ($selected_dept > 0): ?>

        <a href="addPrograms.php?school_id=<?= $selected_school ?>&dept_id=<?= $selected_dept ?>"
           class="btn btn-primary">
            ➕ Add Program
        </a>

        <br><br>

        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">All Programs</div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Short Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($programs) == 0): ?>
                        <tr>
                            <td colspan="4">No programs found for this department.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($programs as $p): ?>
                        <tr>
                            <td><?= $p['progid'] ?></td>
                            <td><?= htmlspecialchars($p['progfullname']) ?></td>
                            <td>
                                <span class="badge badge-blue">
                                    <?= htmlspecialchars($p['progshortname']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="editPrograms.php?id=<?= $p['progid'] ?>&school_id=<?= $selected_school ?>&dept_id=<?= $selected_dept ?>"
                                   class="btn btn-warning btn-xs">Edit</a>

                                <a href="deletePrograms.php?id=<?= $p['progid'] ?>&school_id=<?= $selected_school ?>&dept_id=<?= $selected_dept ?>"
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
        <p>Total of: <?= count($programs) ?> program(s) in the database</p>

    <?php endif; ?>

</div>

<script>
    function validateDeptForm() {
        const deptSelect = document.getElementById('dept_select');
        if (deptSelect.value === '') {
            alert('Please choose a department.');
            return false;
        }
        return true;
    }
</script>

</body>
</html>