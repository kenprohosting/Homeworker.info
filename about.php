<?php
session_start();
$isLoggedIn = isset($_SESSION['employer_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
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
  <div id="hamburger">☰</div>
 <div id="navLinks">
  <nav class="main-nav">
    <ul class="nav-links">
      <li><a class="nav-btn" href="index.php">Home</a></li>
      <li><a class="nav-btn" href="about.php">About</a></li>
      <li><a class="nav-btn" href="contact.php">Contact Us</a></li>
      <li><a class="nav-btn" href="faq.php">FAQ</a></li>
    </ul>
  </nav>
 </div>
</header>


<!-- About Page Content -->
<main>
  <div class="faq-container">
    <div class="faq-title">About Us</div>
    <p><strong>Homeworker Connect</strong> is a Global platform created to bridge the gap between families seeking trusted domestic support and hardworking individuals in search of verified household employment. We aim to bring dignity, professionalism, and convenience to domestic work.</p>

    <div class="faq-title" style="font-size:1.5rem; margin-top:32px;">Our Mission</div>
    <p>To connect employers with verified and reliable househelps, while empowering domestic workers with opportunities, visibility, and safety through a transparent platform.</p>

    <div class="faq-title" style="font-size:1.5rem; margin-top:32px;">Our Values</div>
    <ul>
      <li><strong>Integrity:</strong> We are committed to honest and transparent practices for both employers and workers.</li>
      <li><strong>Empowerment:</strong> We provide opportunities and resources for domestic workers to grow and succeed.</li>
      <li><strong>Trust:</strong> We verify all users to ensure a safe and reliable experience for everyone.</li>
      <li><strong>Support:</strong> Our team is dedicated to helping both employers and workers every step of the way.</li>
    </ul>
  </div>
</main>

<footer>
  <p>&copy; <?= date("Y") ?> KenPro. All rights reserved.</p> | <a href="privacy_policy.php" style="text-decoration: none; color: inherit;">Privacy Policy</a>
</footer>
<script src="hamburger.js"></script>
</body>
</html>
