<?php
session_start();

// Clear admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);

// Destroy the session
session_destroy();

// Redirect to admin login
header("Location: admin_login.php");
exit();
?> 