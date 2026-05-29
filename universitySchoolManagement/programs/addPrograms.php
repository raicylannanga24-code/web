<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

$errors    = [];
$school_id = isset($_GET['school_id']) ? intval($_GET['school_id']) : 0;
$dept_id   = isset($_GET['dept_id'])   ? intval($_GET['dept_id'])   : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $school_id = intval($_POST['school_id']);
    $dept_id   = intval($_POST['dept_id']);
    $id        = trim($_POST['progid']);
    $full      = trim($_POST['progfullname']);
    $short     = trim($_POST['progshortname']);

    // Validate Program ID
    if (empty($id) || !is_numeric($id) || (int)$id <= 0) {
    $errors[] = "Program ID is required and must be a positive number.";
} else {
    $stmt = $pdo->prepare("SELECT * FROM programs WHERE progid = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Program ID $id already exists. Use another.";
    }
}

    // Validate full name
    if (empty($full)) {
        $errors[] = "Full name is required.";
    }

    // Validate short name
    if (empty($short)) {
        $errors[] = "Short name is required.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO programs (progid, progcollid, progcolldeptid, progfullname, progshortname) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$id, $school_id, $dept_id, $full, $short]);
        header("Location: programs.php?school_id=$school_id&dept_id=$dept_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Program</title>
    <link rel="stylesheet" href="../schools.css">
</head>
<body>

<div class="rectangle1">
    <p class="namePage">USJR-SCHOOL MANAGEMENT SYSTEM</p>
    <div class="navMenu">
        <a href="../mydashboard.php">Home</a>
        <a href="../schools.php">School</a>
        <a href="../department.php">Departments</a>
        <a href="../programs.php">Programs</a>
        <a href="../students.php">Students</a>
        <a href="../users.html">Users</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="main-content">

    <div class="topbar">
        <div class="topbar-title">Add New Program</div>
    </div>

    <!-- BACK BUTTON -->
    <a href="programs.php?school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>" class="btn btn-secondary">
        ← Back to Programs
    </a>

    <br><br>

    <?php
    if (!empty($errors)) {
        echo "<ul style='color:red;'>";
        foreach ($errors as $err) {
            echo "<li>$err</li>";
        }
        echo "</ul>";
    }
    ?>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">➕ Add Program</div>
        </div>
        <div class="panel-body">

            <form action="<?= $_SERVER['PHP_SELF'] ?>?school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>" method="post">

                <input type="hidden" name="school_id" value="<?= $school_id ?>">
                <input type="hidden" name="dept_id"   value="<?= $dept_id ?>">

                <div class="form-grid form-grid-3">
                    <div class="form-group">
                        <label>Program ID</label>
                        <input type="number" name="progid"
                            onkeydown="return !['e','E','+','-','.'].includes(event.key)"
                            value="<?= isset($_POST['progid']) ? htmlspecialchars($_POST['progid']) : '' ?>"
                               required>
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="progfullname"
                               value="<?= isset($_POST['progfullname']) ? htmlspecialchars($_POST['progfullname']) : '' ?>"
                               required>
                    </div>
                    <div class="form-group">
                        <label>Short Name</label>
                        <input type="text" name="progshortname"
                               value="<?= isset($_POST['progshortname']) ? htmlspecialchars($_POST['progshortname']) : '' ?>"
                               required>
                    </div>
                </div>

                <br>

                <button type="submit" class="btn btn-primary">Add Program</button>
                <button type="reset" class="btn btn-warning">Reset</button>
                <a href="programs.php?school_id=<?= $school_id ?>&dept_id=<?= $dept_id ?>" class="btn btn-secondary">Cancel</a>

            </form>

        </div>
    </div>

</div>

</body>
</html>