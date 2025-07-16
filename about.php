<?php
session_start();
$isLoggedIn = isset($_SESSION['employer_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>About Us - Homeworker Connect</title>
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

<!-- About Page Content -->
<main>
  <div class="container">
    <h2>About Us</h2>
    <p><strong>Homeworker Connect</strong> is a Kenyan-based platform created to bridge the gap between families seeking trusted domestic support and hardworking individuals in search of verified household employment. We aim to bring dignity, professionalism, and convenience to domestic work.</p>

    <h2>Our Mission</h2>
    <p>To connect employers with verified and reliable househelps, while empowering domestic workers with opportunities, visibility, and safety through a transparent platform.</p>

    <h2>Our Values</h2>
    <ul>
      <li>Integrity: We are committed to honest and transparent practices for both employers and workers.</li>
      <li>Empowerment: We provide opportunities and resources for domestic workers to grow and succeed.</li>
      <li>Trust: We verify all users to ensure a safe and reliable experience for everyone.</li>
      <li>Support: Our team is dedicated to helping both employers and workers every step of the way.</li>
    </ul>
  </div>
</main>

<footer>
  <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
</footer>

</body>
</html>
