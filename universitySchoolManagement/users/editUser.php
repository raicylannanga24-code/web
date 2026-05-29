<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

$user_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE userid = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Store original values for Reset Form
$original = $user;

$msg      = '';
$msg_type = '';

if (isset($_POST['update_user'])) {
    $username        = trim($_POST['username']);
    $usertype        = trim($_POST['usertype']);
    $userrole        = trim($_POST['userrole']);
    $password        = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!$username || !$usertype || !$userrole) {
        $msg      = "Username, User Type, and User Role are required.";
        $msg_type = "danger";
    } elseif (!empty($password) && $password !== $confirm_password) {
        $msg      = "Passwords do not match.";
        $msg_type = "danger";
    } else {
        // Check duplicate username excluding current user
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND userid != ?");
        $check->execute([$username, $user_id]);
        if ($check->fetchColumn() > 0) {
            $msg      = "Username <strong>{$username}</strong> already exists.";
            $msg_type = "danger";
        } else {
            if (!empty($password)) {
                $stmt = $pdo->prepare("UPDATE users SET username=?, usertype=?, userrole=?, password=? WHERE userid=?");
                $r = $stmt->execute([$username, $usertype, $userrole, $password, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username=?, usertype=?, userrole=? WHERE userid=?");
                $r = $stmt->execute([$username, $usertype, $userrole, $user_id]);
            }

            if ($r) {
                header("Location: manageUsers.php?updated=1");
                exit();
            } else {
                $msg      = "Failed to update user.";
                $msg_type = "danger";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Update</title>
<link rel="stylesheet" href="../schools.css">
<style>
    .section-title { font-weight:bold; font-size:1rem; margin:20px 0 10px 0; }
    .form-row { display:flex; align-items:center; gap:16px; margin-bottom:14px; }
    .form-row label { width:200px; font-size:0.95rem; }
    .form-row input, .form-row select { width:400px; padding:7px 10px; border:1px solid #ccc; border-radius:4px; font-size:0.95rem; }
    .btn-reset-pw  { background-color:#4CAF50; color:white; border:none; padding:8px 16px; border-radius:4px; cursor:pointer; margin-bottom:20px; }
    .btn-update    { background-color:#e0e0e0; color:#333; border:1px solid #ccc; padding:8px 16px; border-radius:4px; cursor:pointer; }
    .btn-reset     { background-color:#e0e0e0; color:#333; border:1px solid #ccc; padding:8px 16px; border-radius:4px; cursor:pointer; }
    .btn-exit-link { background-color:#e53935; color:white; border:none; padding:8px 16px; border-radius:4px; cursor:pointer; text-decoration:none; }
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
        <div class="topbar-title">User Update</div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST" id="editForm">

        <!-- Reset Password Button -->
        <button type="button" class="btn-reset-pw"
                onclick="document.getElementById('pw').value='';
                         document.getElementById('cpw').value='';">
            Reset Password
        </button>

        <!-- User Account Details -->
        <div class="section-title">User Account Details</div>

        <div class="form-row">
            <label>User Name:</label>
            <input type="text" name="username"
                   value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="form-row">
            <label>User Type:</label>
            <select name="usertype">
                <option value="Administrator" <?= $user['usertype'] === 'Administrator' ? 'selected' : '' ?>>Administrator</option>
                <option value="User"          <?= $user['usertype'] === 'User'          ? 'selected' : '' ?>>User</option>
            </select>
        </div>

        <div class="form-row">
            <label>User Role:</label>
            <select name="userrole">
                <option value="Administrator" <?= $user['userrole'] === 'Administrator' ? 'selected' : '' ?>>Administrator</option>
                <option value="Creator"       <?= $user['userrole'] === 'Creator'       ? 'selected' : '' ?>>Creator</option>
                <option value="Viewer"        <?= $user['userrole'] === 'Viewer'        ? 'selected' : '' ?>>Viewer</option>
                <option value="Updater"       <?= $user['userrole'] === 'Updater'       ? 'selected' : '' ?>>Updater</option>
                <option value="Remover"       <?= $user['userrole'] === 'Remover'       ? 'selected' : '' ?>>Remover</option>
            </select>
        </div>

        <!-- Password Settings -->
        <div class="section-title">Password Settings</div>

        <div class="form-row">
            <label>User Password:</label>
            <input type="password" name="password" id="pw">
        </div>

        <div class="form-row">
            <label>User Confirm Password:</label>
            <input type="password" name="confirm_password" id="cpw">
        </div>

        <!-- Action Buttons -->
        <div style="display:flex; gap:10px; margin-top:10px;">
            <button type="submit" name="update_user" class="btn-update">Update User Settings</button>
            <button type="button" class="btn-reset" onclick="resetForm()">Reset Form</button>
            <a href="manageUsers.php" class="btn-exit-link">Exit</a>
        </div>

    </form>

</div>

<script>
    // Stores original values to restore on Reset Form
    const original = {
        username : "<?= htmlspecialchars($original['username'], ENT_QUOTES) ?>",
        usertype : "<?= htmlspecialchars($original['usertype'], ENT_QUOTES) ?>",
        userrole : "<?= htmlspecialchars($original['userrole'], ENT_QUOTES) ?>"
    };

    function resetForm() {
        document.querySelector('[name="username"]').value        = original.username;
        document.querySelector('[name="usertype"]').value        = original.usertype;
        document.querySelector('[name="userrole"]').value        = original.userrole;
        document.getElementById('pw').value                      = '';
        document.getElementById('cpw').value                     = '';
    }
</script>

</body>
</html>