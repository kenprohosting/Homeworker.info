<?php
session_start();
$isLoggedIn = isset($_SESSION['employer_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
  <meta charset="UTF-8">
  <title>Contact Us - Homeworker Connect</title>
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
<main>
  <div class="faq-container">
    <div class="faq-title">Contact Us</div>
    <div class="contact-details">
      <div>Email: <a href="mailto:support@homeworkerconnect.info">support@homeworker.info</a></div>
      <div>Phone: +254 700 000 000</div>
      <div>Address: Nairobi, Kenya</div>
    </div>
    <form class="contact-form" method="post" action="#">
      <label for="name">Name</label>
      <input type="text" id="name" name="name" required>
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>
      <label for="message">Message</label>
      <textarea id="message" name="message" required></textarea>
      <button type="submit">Send Message</button>
    </form>
  </div>
</main>
<footer>
  <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
</footer>
</body>
</html> 