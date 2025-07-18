<?php
session_start();
$isLoggedIn = isset($_SESSION['employer_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="favicon.png">
  <meta charset="UTF-8">
  <title>FAQ - Homeworker Connect</title>
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
    <div class="faq-title">Frequently Asked Questions</div>
    <ul class="faq-list">
      <li>
        <div class="faq-q">Who can use Homeworker Connect?</div>
        <div class="faq-a">Anyone in Kenya looking to hire or work as a domestic professional can use our platform.</div>
      </li>
      <li>
        <div class="faq-q">How do I get started?</div>
        <div class="faq-a">Simply register as an employer or employee, complete your profile, and start connecting!</div>
      </li>
      <li>
        <div class="faq-q">Is my information safe?</div>
        <div class="faq-a">Yes, we use secure technology and strict privacy policies to protect your data.</div>
      </li>
      <li>
        <div class="faq-q">How do you verify workers?</div>
        <div class="faq-a">We require ID checks and references for all workers before they are approved on the platform.</div>
      </li>
      <li>
        <div class="faq-q">How can I get support?</div>
        <div class="faq-a">You can reach our support team via the Contact Us page or by emailing <a href="mailto:support@homeworkerconnect.co.ke">support@homeworkerconnect.co.ke</a>.</div>
      </li>
    </ul>
  </div>
</main>
<footer>
  <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
</footer>
</body>
</html> 