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
</head>
<body>

<header>
    <div class="logo">
        <img src="bghse.png" alt="Logo" style="height: 40px;">
    </div>
    <nav class="main-nav">
        <ul class="nav-links">
            <li><a class="nav-btn" href="index.php">Home</a></li>
            <li><a class="nav-btn" href="about.php">About</a></li>
            <li><a class="nav-btn" href="contact.php">Contact Us</a></li>
            <li><a class="nav-btn" href="faq.php">FAQ</a></li>
        </ul>
    </nav>
</header>

<div class="form-container">
    <h2>Employer Login</h2>

    <?php
    if ($errors) foreach ($errors as $e) echo "<p class='error'>$e</p>";
    ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="btn">Login</button>
    </form>
    <p style="text-align:center; margin-top:10px;">
        <a href="forgot_password.php?type=employer">Forgot Password?</a>
    </p>
</div>

<footer>
    <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
</footer>

</body>
</html>
