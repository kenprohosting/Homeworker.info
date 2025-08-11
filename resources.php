<?php
session_start();
$isLoggedIn = isset($_SESSION['employer_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
  <meta charset="UTF-8">
  <title>Resources - Homeworker Connect</title>
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
      <li><a class="nav-btn" href="resources.php">Resources</a></li>
      <li><a class="nav-btn" href="contact.php">Contact Us</a></li>
      <li><a class="nav-btn" href="faq.php">FAQ</a></li>
    </ul>
  </nav>
 </div>
</header>


<!-- About Page Content -->
<main>
  <div class="faq-container" style= "padding:0px; margin-top : 0px; margin-left : 10%; margin-right : 10%; width: 100%;">
          <iframe src="https://homeworker.info/resources/" 
            style="border: none; width: 100%; height: 200vh; display: block;" 
            scrolling="auto"></iframe>

  </div>
</main>

<footer>
  <p>&copy; <?= date("Y") ?> KenPro. All rights reserved.</p> | <a href="privacy_policy.php" style="text-decoration: none; color: inherit;">Privacy Policy</a>
</footer>
<script src="hamburger.js"></script>
</body>
</html>
