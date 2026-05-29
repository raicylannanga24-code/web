<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

$prog_id   = isset($_GET['id'])        ? intval($_GET['id'])        : 0;
$school_id = isset($_GET['school_id']) ? intval($_GET['school_id']) : 0;
$dept_id   = isset($_GET['dept_id'])   ? intval($_GET['dept_id'])   : 0;
$errors = [];

// Fetch existing program data to pre-fill the form
$stmt = $pdo->prepare("SELECT * FROM programs WHERE progid = ?");
$stmt->execute([$prog_id]);
$prog = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prog) {
    echo "Program not found.";
    exit();
}

// Extract only the program suffix (last part after dept_id)
// e.g. progid=1001001, dept_id=1001 → suffix = "001"
$dept_id_len     = strlen((string)$dept_id);
$current_suffix  = substr((string)$prog_id, $dept_id_len); // e.g. "001"

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_suffix = trim($_POST['prog_suffix']);
    $fullname   = trim($_POST['progfullname']);
    $shortname  = trim($_POST['progshortname']);

    // Build the new full program ID
    $new_progid = intval($dept_id . $new_suffix);

    if (empty($new_suffix) || !is_numeric($new_suffix)) {
        $errors[] = "Program suffix is required and must be a number.";
    }
    if (empty($fullname)) {
        $errors[] = "Full name is required.";
    }
    if (empty($shortname)) {
        $errors[] = "Short name is required.";
    }

    // Check if new prog ID already exists (only if changed)
    if (empty($errors) && $new_progid != $prog_id) {
        $check = $pdo->prepare("SELECT progid FROM programs WHERE progid = ?");
        $check->execute([$new_progid]);
        if ($check->fetch()) {
            $errors[] = "Program ID $new_progid already exists.";
        }
    }

    if (empty($errors)) {
    if (
        $new_progid == $prog_id &&
        $fullname   == $prog['progfullname'] &&
        $shortname  == $prog['progshortname']
    ) {
        $errors[] = "Nothing to update. No changes were made.";
    }
}

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

            $update_prog = $pdo->prepare(
                "UPDATE programs SET progid = ?, progfullname = ?, progshortname = ? WHERE progid = ?"
            );
            $update_prog->execute([$new_progid, $fullname, $shortname, $prog_id]);

            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            $pdo->commit();
            header("Location: programs.php?school_id=$school_id&dept_id=$dept_id");
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
    <title>Edit Program</title>
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
        <div class="topbar-title">Edit Program</div>
    </div>

    <a href="programs.php?school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>" class="btn btn-secondary">
        ← Back to Programs
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
            <div class="panel-title">✏️ Edit Program</div>
        </div>
        <div class="panel-body">

            <form action="<?= $_SERVER['PHP_SELF'] ?>?id=<?= $prog_id ?>&school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>" method="POST">

                <input type="hidden" name="prog_id"   value="<?= $prog_id ?>">
                <input type="hidden" name="school_id" value="<?= $school_id ?>">
                <input type="hidden" name="dept_id"   value="<?= $dept_id ?>">

                <div class="form-grid form-grid-3">
                    <div class="form-group">
                        <label>School ID <small>(locked)</small></label>
                        <input type="text" value="<?= $school_id ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Department ID <small>(locked)</small></label>
                        <input type="text" value="<?= $dept_id ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Program ID <small>(editable part)</small></label>
                        <input type="text" name="prog_suffix"
                               onkeydown="return !['e','E','+','-','.'].includes(event.key)"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                value="<?= htmlspecialchars($_POST['prog_suffix'] ?? $current_suffix) ?>"
                                required maxlength="10">
                        <small style="color:#888;">
                            Full Program ID will be: <strong><?= $dept_id ?></strong> + your suffix
                        </small>
                    </div>
                </div>

                <div class="form-grid form-grid-3" style="margin-top:12px;">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="progfullname"
                               value="<?= htmlspecialchars($_POST['progfullname'] ?? $prog['progfullname']) ?>"
                               required>
                    </div>
                    <div class="form-group">
                        <label>Short Name</label>
                        <input type="text" name="progshortname"
                               value="<?= htmlspecialchars($_POST['progshortname'] ?? $prog['progshortname']) ?>"
                               required>
                    </div>
                </div>

                <br>

                <button type="submit" class="btn btn-primary">💾 Update Program</button>
                <a href="programs.php?school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>" class="btn btn-secondary">Cancel</a>

            </form>

        </div>
    </div>

</div>

</body>
</html>