<?php
session_start();
require_once('db_connect.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM employer WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['employer_id'] = $user['ID'];
        $_SESSION['employer_name'] = $user['Name'];
        header("Location:employer_dashboard.php");
        exit();
    } else {
        $errors[] = "Invalid login credentials.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employer Login - Homeworker Connect</title>
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
        border: 2px solid #111;
        padding: 18px 16px 0 16px;
      }
      .form-container > p:last-of-type {
        margin-bottom: 0 !important;
      }
      .form-container > *:last-child {
        margin-bottom: 0 !important;
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
        <h2>Employer Login</h2>
        <?php if (!empty($errors)) foreach ($errors as $e) echo "<p class='error'>$e</p>"; ?>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Login</button>
        </form>
        <p style="text-align:center; margin-top:10px;">
            <a href="forgot_password.php?type=employer">Forgot Password?</a>
        </p>
        <p style="text-align:center; margin-top:10px;">
            Don't have an account? <a href="employer_register.php">Register here</a>
        </p>
    </div>
    <footer>
        <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
    </footer>

