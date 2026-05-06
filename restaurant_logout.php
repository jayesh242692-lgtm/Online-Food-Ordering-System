<?php
session_start();
unset($_SESSION['restaurant_id']);
unset($_SESSION['restaurant_name']);
header("Location: restaurant_login.php");
exit();
?>
