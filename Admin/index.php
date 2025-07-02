<?php
session_start();

// Define the admin password (hashed)
// (Note: $stored_hash line below is not needed anymore, so we'll remove it)

// This is your real stored hashed password
$admin_password_hash = '$2y$10$3WpPCNnbmCx5TXmTA.NjMea5ADSfHcpmF76bGH6KAc362TWjat0aa';

$login_error = "";

// Check for logout message
$logout_message = "";
if (isset($_GET['logout'])) {
    $logout_message = "✅ You have been logged out successfully.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_pass = $_POST['password'];

    if (password_verify($entered_pass, $admin_password_hash)) {
        $_SESSION['admin_logged_in'] = true;
        header("Location:admin_dashboard.php");
        exit();
    } else {
        $login_error = "Incorrect password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Login</title>
</head>
<body>
  <h2>Admin Login</h2>

  <?php if ($logout_message): ?>
    <p style="color:green;"><?= $logout_message ?></p>
  <?php endif; ?>

  <?php if ($login_error): ?>
    <p style="color:red;"><?= $login_error ?></p>
  <?php endif; ?>

  <form method="POST">
    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>
    <button type="submit">Login</button>
  </form>
</body>
</html>
