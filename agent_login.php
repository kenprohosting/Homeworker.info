<?php
session_start();
require_once 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password FROM agents WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt;
        
        if ($result->rowCount() == 1) {
            $agent = $result->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $agent['password'])) {
                $_SESSION['agent_id'] = $agent['id'];
                $_SESSION['agent_name'] = $agent['name'];
                $_SESSION['agent_email'] = $agent['email'];
                header("Location: agent_dashboard.php");
                exit();
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Agent not found';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Login - Homeworker Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <style>
      body { background: #f4f8fb; font-family: 'Segoe UI', Arial, sans-serif; }
      .form-container {
        max-width: 400px;
        margin: 40px auto 0 auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        border: 2.5px solid #111 !important;
        padding: 18px 16px 0 16px;
        padding-bottom: 0 !important;
      }
      .form-container > *:last-child,
      .form-container p:last-of-type {
        margin-bottom: 0 !important;
        margin-top: 0 !important;
        padding-bottom: 0 !important;
      }
      .form-container p {
        margin-bottom: 0;
      }
      .form-container h2 {
        text-align: center;
        color: #197b88;
        margin-bottom: 18px;
      }
      .form-container form {
        display: grid;
        gap: 18px;
      }
      .form-container p:last-of-type {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
      }
      .form-container p {
        margin-bottom: 0.5em;
      }
      .form-container input[type="email"],
      .form-container input[type="password"] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #cfd8dc;
        border-radius: 6px;
        font-size: 1rem;
        background: #f9fbfc;
        transition: border 0.2s;
      }
      .form-container input[type="email"]:focus,
      .form-container input[type="password"]:focus {
        border: 1.5px solid #197b88;
        outline: none;
        background: #fff;
      }
      .btn {
        background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%);
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 12px 0;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        margin-top: 8px;
        transition: background 0.2s;
      }
      .btn:hover {
        background: linear-gradient(90deg, #1ec8c8 0%, #197b88 100%);
      }
      .error {
        background: #ffeaea;
        color: #c0392b;
        border-left: 4px solid #c0392b;
        padding: 10px 14px;
        border-radius: 6px;
        margin-bottom: 10px;
      }
      footer { text-align: center; margin-top: 30px; color: #888; }
    </style>
</head>
<body>
    <div style="width:100%;text-align:center;margin:0;padding:0;">
        <img src="bghse.png" alt="Logo" style="height:48px;display:inline-block;margin:0 auto 0 auto;padding-top:8px;">
    </div>
    <div class="form-container">
        <a href="index.php" style="display:inline-block;margin-bottom:10px;color:#197b88;text-decoration:none;font-weight:500;">&larr; Back</a>
        <h2>Agent Login</h2>
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Login</button>
        </form>
        <p style="text-align:center; margin-top:10px;">
            <a href="forgot_password.php?type=agent">Forgot Password?</a>
        </p>
        <p style="text-align:center; margin-top:10px;">
            Don't have an account? <a href="agent_register.php">Register here</a>
        </p>
    </div>

    <footer>
        <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
    </footer>
</body>
</html> 