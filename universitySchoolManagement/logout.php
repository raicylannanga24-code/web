<?php
session_start();
session_destroy();

header("Location: mylogin.php");
exit();
?>