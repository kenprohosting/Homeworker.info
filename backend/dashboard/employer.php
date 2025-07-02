<?php
session_start();
if ($_SESSION['role'] !== 'employer') {
    header("Location: ../login.php");
    exit;
}

echo "Welcome, " . $_SESSION['user']['Name'] . "!<br>";
// You can now create bookings, view employees, etc.
?>
