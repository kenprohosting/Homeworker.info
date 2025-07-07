<?php
session_start();
date_default_timezone_set('Africa/Nairobi');
require_once 'db_connect.php';

// PHPMailer manual include
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$type = isset($_GET['type']) && in_array($_GET['type'], ['employee', 'employer']) ? $_GET['type'] : 'employee';
$table = $type;

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
        $update = $conn->prepare("UPDATE $table SET reset_token=?, reset_token_expiry=? WHERE email=?");
        $update->execute([$token, $expiry, $email]);
        $reset_link = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?type=$type&token=$token";

        // PHPMailer setup
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Or your SMTP server
            $mail->SMTPAuth   = true;
            $mail->Username   = 'ethiopianmark@gmail.com'; // Your email
            $mail->Password   = 'bfok yqfu fjpf jlcf';    // App password (not your Gmail password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('ethiopianmark@gmail.com', 'HouseHelp');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "Click the following link to reset your password: <a href='$reset_link'>$reset_link</a><br>This link will expire in 1 hour.";

            $mail->send();
            $success = "A password reset link has been sent to your email.";
        } catch (Exception $e) {
            $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "No account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="form-container">
    <h2>Forgot Password (<?= ucfirst($type) ?>)</h2>
    <?php if ($success): ?>
        <p class="success"><?= $success ?></p>
    <?php elseif ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit" class="btn">Send Reset Link</button>
    </form>
    <p style="text-align:center; margin-top:10px;">
        <a href="<?= $type ?>_login.php">Back to Login</a>
    </p>
</div>
</body>
</html> 