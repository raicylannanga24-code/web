<?php
session_start(); // start session

// check login
if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php"); // redirect login
    exit();
}

include '../database.php'; // database connection

$errors = []; // store errors

// get school id
$school_id = isset($_GET['school_id']) ? intval($_GET['school_id']) : 0;

// form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // inputs
    $school_id = intval($_POST['school_id']);
    $id        = trim($_POST['deptid']);
    $full      = trim($_POST['deptfullname']);
    $short     = trim($_POST['deptshortname']);

    // validate id
    if (empty($id) || !is_numeric($id) || (int)$id <= 0) {

        $errors[] = "Department ID is required and must be a positive number.";

    } else {

        // duplicate check
        $stmt = $pdo->prepare("SELECT * FROM departments WHERE deptid = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            $errors[] = "Department ID $id already exists. Use another.";
        }
    }

    // validate fullname
    if (empty($full)) {
        $errors[] = "Full name is required.";
    }

    // validate shortname
    if (empty($short)) {
        $errors[] = "Short name is required.";
    }

    // save data
    if (empty($errors)) {

        // insert query
        $stmt = $pdo->prepare(
            "INSERT INTO departments (deptid, deptcollid, deptfullname, deptshortname) VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([$id, $school_id, $full, $short]);

        // redirect
        header("Location: department.php?school_id=$school_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <!-- title -->
    <title>Add Department</title>

    <!-- css -->
    <link rel="stylesheet" href="../schools.css">
</head>
<body>

<!-- navbar -->
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

<!-- content -->
<div class="main-content">

    <!-- topbar -->
    <div class="topbar">
        <div class="topbar-title">Add New Department</div>
    </div>

    <!-- back button -->
    <a href="department.php?school_id=<?= $school_id ?>" class="btn btn-secondary">
        ← Back to Departments
    </a>

    <br><br>

    <!-- error display -->
    <?php
    if (!empty($errors)) {

        echo "<ul style='color:red;'>";

        foreach ($errors as $err) {
            echo "<li>$err</li>";
        }

        echo "</ul>";
    }
    ?>

    <!-- panel -->
    <div class="panel">

        <div class="panel-header">
            <div class="panel-title">➕ Add Department</div>
        </div>

        <div class="panel-body">

            <!-- form -->
            <form action="<?= $_SERVER['PHP_SELF'] ?>?school_id=<?= $school_id ?>" method="post">

                <!-- hidden input -->
                <input type="hidden" name="school_id" value="<?= $school_id ?>">

                <!-- inputs -->
                <div class="form-grid form-grid-3">

                    <!-- department id -->
                    <div class="form-group">
                        <label>Department ID</label>

                        <input type="number"
                               name="deptid"
                               onkeydown="return !['e','E','+','-','.'].includes(event.key)"
                               value="<?= isset($_POST['deptid']) ? htmlspecialchars($_POST['deptid']) : '' ?>"
                               required>
                    </div>

                    <!-- fullname -->
                    <div class="form-group">
                        <label>Full Name</label>

                        <input type="text"
                               name="deptfullname"
                               value="<?= isset($_POST['deptfullname']) ? htmlspecialchars($_POST['deptfullname']) : '' ?>"
                               required>
                    </div>

                    <!-- shortname -->
                    <div class="form-group">
                        <label>Short Name</label>

                        <input type="text"
                               name="deptshortname"
                               value="<?= isset($_POST['deptshortname']) ? htmlspecialchars($_POST['deptshortname']) : '' ?>"
                               required>
                    </div>

                </div>

                <br>

                <!-- buttons -->
                <button type="submit" class="btn btn-primary">Add Department</button>

                <button type="reset" class="btn btn-warning">Reset</button>

                <a href="department.php?school_id=<?= $school_id ?>" class="btn btn-secondary">
                    Cancel
                </a>

            </form>

        </div>
    </div>

</div>

</body>
</html>