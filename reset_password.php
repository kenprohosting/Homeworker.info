<?php
session_start();
date_default_timezone_set('Africa/Nairobi');
require_once 'db_connect.php';

$type = isset($_GET['type']) && in_array($_GET['type'], ['employee', 'employer']) ? $_GET['type'] : 'employee';
$table = $type;
$token = isset($_GET['token']) ? $_GET['token'] : '';

$error = '';
$success = '';
$show_form = true;

if (!$token) {
    $error = 'Invalid or missing token.';
    $show_form = false;
} else {
    $stmt = $conn->prepare("SELECT * FROM $table WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        $error = 'Invalid or expired token.';
        $show_form = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $show_form) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE $table SET password_hash=?, reset_token=NULL, reset_token_expiry=NULL WHERE reset_token=?");
        $update->execute([$hash, $token]);
        $success = 'Your password has been reset. <br><a href="' . $type . '_login.php" class="btn">Go to Login</a>';
        $show_form = false;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="favicon.png">
    <title>Reset Password</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="form-container">
    <h2>Reset Password (<?= ucfirst($type) ?>)</h2>
    <?php if ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="success"><?= $success ?></p>
    <?php endif; ?>
    <?php if ($show_form): ?>
    <form method="POST">
        <input type="password" name="password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit" class="btn">Reset Password</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html> 