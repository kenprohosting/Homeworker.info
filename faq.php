<?php
session_start();
$isLoggedIn = isset($_SESSION['employer_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
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
<main>
  <div class="faq-container">
    <div class="faq-title">Frequently Asked Questions</div>
    <ul class="faq-list">
      <li>
        <div class="faq-q">Who can use Homeworker Connect?</div>
        <div class="faq-a">Anyone in the world looking to hire or work as a domestic professional can use our platform.</div>
      </li>
      <li>
        <div class="faq-q">How do I register as an employer?</div>
        <div class="faq-a">Go to the employer registration page, fill in your details, pay the KES 10 registration fee via IntaSend, and complete your profile.</div>
      </li>
      <li>
        <div class="faq-q">How do I register as an agent?</div>
        <div class="faq-a">Visit the agent registration page, provide your information, agree to the Agent Terms and Conditions, and submit your registration.</div>
      </li>
      <li>
        <div class="faq-q">How does the matching process work?</div>
        <div class="faq-a">Employers post job requirements, and verified workers or agents can apply. You can browse profiles and connect directly.</div>
      </li>
      <li>
        <div class="faq-q">What payment methods are accepted?</div>
        <div class="faq-a">We use IntaSend for secure payments, supporting M-Pesa and other methods for fees like registration and accessing contact details.</div>
      </li>
      <li>
        <div class="faq-q">How is my information kept safe?</div>
        <div class="faq-a">We use secure technology, encryption, and strict privacy policies to protect your data. Read our Privacy Policy for details.</div>
      </li>
      <li>
        <div class="faq-q">How are workers verified?</div>
        <div class="faq-a">We require ID/passport checks, references, and background verification before approving workers on the platform.</div>
      </li>
      <li>
        <div class="faq-q">What if I encounter an issue with a worker or employer?</div>
        <div class="faq-a">Contact our support team immediately. We have dispute resolution processes in place.</div>
      </li>
      <li>
        <div class="faq-q">How can I get support?</div>
        <div class="faq-a">Reach our support team via the Contact Us page or email <a href="mailto:support@homeworkerconnect.info">support@homeworkerconnect.info</a>.</div>
      </li>
      <li>
        <div class="faq-q">Can I update my profile after registration?</div>
        <div class="faq-a">Yes, log in to your account and edit your profile information at any time.</div>
      </li>
    </ul>
  </div>
</main>
<footer>
  <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p> | <a href="privacy_policy.php" style="text-decoration: none; color: inherit;">Privacy Policy</a>
</footer>
<script src="hamburger.js"></script>
</body>
</html>