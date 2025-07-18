<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once('db_connect.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM employees WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['employee_id'] = $user['id'];
            $_SESSION['employee_name'] = $user['name'];
            header("Location: employee_dashboard.php");
            exit();
        } else {
            $errors[] = "Invalid email or password.";
        }
    } else {
        $errors[] = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="favicon.png">
    <title>Employee Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div style="width:100%;text-align:center;margin:0;padding:0;">
        <img src="bghse.png" alt="Logo" style="height:48px;display:inline-block;margin:0 auto 0 auto;padding-top:8px;">
    </div>
    <div class="form-container">
        <a href="index.php" style="display:inline-block;margin-bottom:10px;color:#197b88;text-decoration:none;font-weight:500;">&larr; Back</a>
        <h2>Employee Login</h2>
        <?php if ($errors) foreach ($errors as $e) echo "<p class='error'>$e</p>"; ?>
        <form method="POST" action="employee_login.php">
            <input type="email" name="email" placeholder="Email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
            <div style="position:relative;">
              <input type="password" name="password" id="password" placeholder="Password" required style="padding-right:36px;">
              <span onclick="togglePassword('password', this)" style="position:absolute;top:50%;right:10px;transform:translateY(-50%);cursor:pointer;font-size:1.2em;">
                &#128065;
              </span>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <p style="text-align:center; margin-top:10px;">
            <a href="forgot_password.php?type=employee">Forgot Password?</a>
        </p>
    </div>
    <footer>
        <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
<script>
function togglePassword(id, el) {
  var input = document.getElementById(id);
  if (input.type === "password") {
    input.type = "text";
    el.innerHTML = "&#128064;";
  } else {
    input.type = "password";
    el.innerHTML = "&#128065;";
  }
}
</script>
</footer>
</body>
</html>
<style>
  body { background: #f4f8fb; font-family: 'Segoe UI', Arial, sans-serif; }
  /* Removed .login-header and .logo-centered styles */
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
