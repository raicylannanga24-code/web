<?php
session_start();
include 'database.php';

$error = "";

if (isset($_POST['login'])) {//check if ang form na submit na jud

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");//search for the user sa database
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && $user['password'] === $password) {//user seen, open dashboard
        $_SESSION['username'] = $user['username'];
        $_SESSION['userrole'] = $user['userrole'];  
        header("Location: mydashboard.php");
        exit();
    } else {
        $error = "Invalid Username or Password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - USJR System</title>
    <link rel="stylesheet" href="login.css">
</head>

<body class="login-page">

<div class="topBar">
    <div class="namePage">USJR-SCHOOL MANAGEMENT SYSTEM</div>
</div>

<div class="loginContainer">
    <div class="loginBox">
        <div class="loginTitle">Login to Your Account</div>

        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" name="login">LOGIN</button>
        </form>

        <?php if ($error != ""): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
