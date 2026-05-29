<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

$id     = $_GET['id'];
$errors = [];

// fetch existing school data
$stmt = $pdo->prepare("SELECT * FROM colleges WHERE collid = ?");
$stmt->execute([$id]);
$school = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$school) {
    die("School not found.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_id = trim($_POST['collid']);
    $full   = trim($_POST['collfullname']);
    $short  = trim($_POST['collshortname']);

    // Validate fields
    if (empty($new_id)) {
        $errors[] = "School ID is required.";
    } elseif (!is_numeric($new_id)) {
        $errors[] = "School ID must be a number.";
    }
    if (empty($full)) {
        $errors[] = "Full name is required.";
    }
    if (empty($short)) {
        $errors[] = "Short name is required.";
    }

    // Check if new ID already exists (only if ID is being changed)
    if (empty($errors) && $new_id != $id) {
        $check = $pdo->prepare("SELECT collid FROM colleges WHERE collid = ?");
        $check->execute([$new_id]);
        if ($check->fetch()) {
            $errors[] = "School ID $new_id already exists.";
        }
    }

    if (empty($errors)) {
    if (
        $new_id == $id &&
        $full   == $school['collfullname'] &&
        $short  == $school['collshortname']
    ) {
        $errors[] = "Nothing to update. No changes were made.";
    }
}

    if (empty($errors)) {
    try {
        $pdo->beginTransaction();
        
        // Disable foreign key checks temporarily
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        if ($new_id != $id) {
            // Fetch all departments under this college
            $depts = $pdo->prepare("SELECT deptid FROM departments WHERE deptcollid = ?");
            $depts->execute([$id]);
            $dept_rows = $depts->fetchAll(PDO::FETCH_ASSOC);

            foreach ($dept_rows as $dept) {
                $old_deptid = $dept['deptid'];

                $old_id_len  = strlen((string)$id);
                $dept_suffix = substr((string)$old_deptid, $old_id_len);
                $new_deptid  = $new_id . $dept_suffix;

                // Fetch all programs under this department
                $progs = $pdo->prepare("SELECT progid FROM programs WHERE progcolldeptid = ?");
                $progs->execute([$old_deptid]);
                $prog_rows = $progs->fetchAll(PDO::FETCH_ASSOC);

                foreach ($prog_rows as $prog) {
                    $old_progid  = $prog['progid'];
                    $dept_id_len = strlen((string)$old_deptid);
                    $prog_suffix = substr((string)$old_progid, $dept_id_len);
                    $new_progid  = $new_deptid . $prog_suffix;

                    $update_prog = $pdo->prepare(
                        "UPDATE programs SET progid = ?, progcollid = ?, progcolldeptid = ? WHERE progid = ?"
                    );
                    $update_prog->execute([$new_progid, $new_id, $new_deptid, $old_progid]);
                }

                $update_dept = $pdo->prepare(
                    "UPDATE departments SET deptid = ?, deptcollid = ? WHERE deptid = ?"
                );
                $update_dept->execute([$new_deptid, $new_id, $old_deptid]);
            }
        }

        // Update the college itself
        $update_college = $pdo->prepare(
            "UPDATE colleges SET collid = ?, collfullname = ?, collshortname = ? WHERE collid = ?"
        );
        $update_college->execute([$new_id, $full, $short, $id]);

        // Re-enable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        $pdo->commit();
        header("Location: schools.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        // Make sure FK checks are re-enabled even on failure
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
<title>Edit School</title>
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
        <div class="topbar-title">Edit School</div>
    </div>

    <?php if (!empty($errors)): ?>
        <ul style='color:red;'>
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">✏️ Edit School</div>
        </div>
        <div class="panel-body">

            <form action="<?= $_SERVER['PHP_SELF'] ?>?id=<?= $id ?>" method="post">

                <div class="form-grid form-grid-3">
                    <div class="form-group">
                        <label>School ID</label>
                        <input type="number" name="collid"
                               value="<?= htmlspecialchars($_POST['collid'] ?? $school['collid']) ?>"
                                onkeydown="return !['e','E','+','-','.'].includes(event.key)" required>
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="collfullname"
                               value="<?= htmlspecialchars($_POST['collfullname'] ?? $school['collfullname']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Short Name</label>
                        <input type="text" name="collshortname"
                               value="<?= htmlspecialchars($_POST['collshortname'] ?? $school['collshortname']) ?>" required>
                    </div>
                </div>

                <br>

                <button type="submit" class="btn btn-primary">Update School</button>
                <button type="reset" class="btn btn-warning">Reset</button>
                <a href="schools.php" class="btn btn-secondary">Cancel</a>

            </form>

        </div>
    </div>

</div>

</body>
</html>