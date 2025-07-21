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

    $stmt = $conn->prepare("SELECT * FROM employees WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (!isset($user['Password_hash']) || $user['Password_hash'] === null) {
            $errors[] = "Password hash not found for this user.";
        } elseif (!password_verify($password, $user['Password_hash'])) {
            $errors[] = "Password does not match.";
        } else {
            $_SESSION['employee_id'] = $user['ID'];
            $_SESSION['employee_name'] = $user['Name'];
            header("Location: employee_dashboard.php");
            exit();
        }
    } else {
        $errors[] = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <title>Employee Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body style="background: #f4f8fb; font-family: 'Segoe UI', Arial, sans-serif; display: flex; flex-direction: column; min-height: 100vh;">
    <div style="width:100%;text-align:center;margin:0;padding:0;">
        <img src="bghse.png" alt="Logo" style="height:48px;display:inline-block;margin:16px auto 0 auto;">
    </div>
    <div class="form-container" style="max-width: 360px; margin: 24px auto; background: #ffffff; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); padding: 24px; display: flex; flex-direction: column; gap: 16px;">
        <a href="index.php" style="color: #197b88; text-decoration: none; font-weight: 500; align-self: flex-start;">&larr; Back</a>
        <h2 style="text-align: center; color: #197b88; margin: 0; font-size: 1.5rem;">Employee Login</h2>
        <?php if ($errors) foreach ($errors as $e) echo "<p style='background: #ffeaea; color: #c0392b; padding: 8px 12px; border-radius: 8px; margin: 0; text-align: center;'>$e</p>"; ?>
        <form method="POST" action="employee_login.php" style="display: flex; flex-direction: column; gap: 12px;">
            <input type="email" name="email" placeholder="Email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
            <div style="position: relative;">
                <input type="password" name="password" id="password" placeholder="Password" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s; width: 100%; box-sizing: border-box;">
                <span onclick="togglePassword('password', this)" style="position: absolute; top: 50%; right: 12px; transform: translateY(-50%); cursor: pointer; font-size: 1.2em;">
                    &#128065;
                </span>
            </div>
            <button type="submit" style="background: linear-gradient(135deg, #197b88, #1ec8c8); color: #fff; border: none; border-radius: 8px; padding: 12px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: background 0.3s;">Login</button>
        </form>
        <p style="text-align: center; margin: 0; font-size: 0.9rem;">
            <a href="forgot_password.php?type=employee" style="color: #197b88; text-decoration: none;">Forgot Password?</a>
        </p>
    </div>
    <footer style="text-align:center;margin-top:auto;color:#888;">
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
  body { background: #f4f8fb; font-family: 'Segoe UI', Arial, sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
  .form-container input:focus {
    border-color: #197b88;
  }
  .form-container button:hover {
    background: linear-gradient(135deg, #1ec8c8, #197b88);
  }
  footer { margin-top: auto; text-align: center; color: #888; padding: 16px 0; }
</style>
