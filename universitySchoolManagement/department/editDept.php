<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

$dept_id   = isset($_GET['id'])        ? intval($_GET['id'])        : 0;
$school_id = isset($_GET['school_id']) ? intval($_GET['school_id']) : 0;
$errors = [];

// Fetch existing department data to pre-fill the form
$stmt = $pdo->prepare("SELECT * FROM departments WHERE deptid = ?");
$stmt->execute([$dept_id]);
$dept = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dept) {
    echo "Department not found.";
    exit();
}

// Extract the suffix (e.g. 1001 with school_id=1 → suffix = "001")
$school_id_len  = strlen((string)$school_id);
$current_suffix = substr((string)$dept_id, $school_id_len); // e.g. "001"

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_suffix = trim($_POST['dept_suffix']);
    $fullname   = trim($_POST['deptfullname']);
    $shortname  = trim($_POST['deptshortname']);

    // Build the new full department ID
    $new_deptid = intval($school_id . $new_suffix);

    if (empty($new_suffix) || !is_numeric($new_suffix)) {
        $errors[] = "Department suffix is required and must be a number.";
    }
    if (empty($fullname)) {
        $errors[] = "Full name is required.";
    }
    if (empty($shortname)) {
        $errors[] = "Short name is required.";
    }

    // Check if new dept ID already exists (only if changed)
    if (empty($errors) && $new_deptid != $dept_id) {
        $check = $pdo->prepare("SELECT deptid FROM departments WHERE deptid = ?");
        $check->execute([$new_deptid]);
        if ($check->fetch()) {
            $errors[] = "Department ID $new_deptid already exists.";
        }
    }

    if (empty($errors)) {
    // Check if nothing changed
    if (
        $new_deptid == $dept_id &&
        $fullname   == $dept['deptfullname'] &&
        $shortname  == $dept['deptshortname']
    ) {
        $errors[] = "Nothing to update. No changes were made.";
    }
}


    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

            if ($new_deptid != $dept_id) {
                // Fetch all programs under this department
                $progs = $pdo->prepare("SELECT progid FROM programs WHERE progcolldeptid = ?");
                $progs->execute([$dept_id]);
                $prog_rows = $progs->fetchAll(PDO::FETCH_ASSOC);

                foreach ($prog_rows as $prog) {
                    $old_progid  = $prog['progid'];
                    $dept_id_len = strlen((string)$dept_id);
                    $prog_suffix = substr((string)$old_progid, $dept_id_len); // e.g. "001"
                    $new_progid  = $new_deptid . $prog_suffix;               // e.g. "1002001"

                    $update_prog = $pdo->prepare(
                        "UPDATE programs SET progid = ?, progcolldeptid = ? WHERE progid = ?"
                    );
                    $update_prog->execute([$new_progid, $new_deptid, $old_progid]);
                }

                // Update the department ID
                $update_dept = $pdo->prepare(
                    "UPDATE departments SET deptid = ?, deptfullname = ?, deptshortname = ? WHERE deptid = ?"
                );
                $update_dept->execute([$new_deptid, $fullname, $shortname, $dept_id]);

            } else {
                // ID unchanged, just update names
                $update_dept = $pdo->prepare(
                    "UPDATE departments SET deptfullname = ?, deptshortname = ? WHERE deptid = ?"
                );
                $update_dept->execute([$fullname, $shortname, $dept_id]);
            }

            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            $pdo->commit();
            header("Location: department.php?school_id=$school_id");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            $errors[] = "Update failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Department</title>
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
        <div class="topbar-title">Edit Department</div>
    </div>

    <a href="department.php?school_id=<?= $school_id ?>" class="btn btn-secondary">
        ← Back to Departments
    </a>

    <br><br>

    <?php if (!empty($errors)): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">✏️ Edit Department</div>
        </div>
        <div class="panel-body">

            <form action="<?= $_SERVER['PHP_SELF'] ?>?id=<?= $dept_id ?>&school_id=<?= $school_id ?>" method="POST">

                <input type="hidden" name="dept_id"   value="<?= $dept_id ?>">
                <input type="hidden" name="school_id" value="<?= $school_id ?>">

                <div class="form-grid form-grid-3">
                    <div class="form-group">
                        <label>School ID <small>(locked)</small></label>
                        <!-- School ID prefix is locked -->
                        <input type="text" value="<?= $school_id ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Department Suffix <small>(editable part)</small></label>
                        <!-- Only the suffix is editable e.g. "001" -->
                        <input type="text" name="dept_suffix"
                               value="<?= htmlspecialchars($_POST['dept_suffix'] ?? $current_suffix) ?>" 
                               onkeydown="return !['e','E','+','-','.'].includes(event.key)"
                               required maxlength="10">
                        <small style="color:#888;">
                            Full Dept ID will be: <strong><?= $school_id ?></strong> + your suffix
                        </small>
                    </div>
                </div>

                <div class="form-grid form-grid-3" style="margin-top:12px;">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="deptfullname"
                               value="<?= htmlspecialchars($_POST['deptfullname'] ?? $dept['deptfullname']) ?>"
                               required>
                    </div>
                    <div class="form-group">
                        <label>Short Name</label>
                        <input type="text" name="deptshortname"
                               value="<?= htmlspecialchars($_POST['deptshortname'] ?? $dept['deptshortname']) ?>"
                               required>
                    </div>
                </div>

                <br>

                <button type="submit" class="btn btn-primary">💾 Update Department</button>
                <a href="department.php?school_id=<?= $school_id ?>" class="btn btn-secondary">Cancel</a>

            </form>

        </div>
    </div>

</div>

</body>
</html>