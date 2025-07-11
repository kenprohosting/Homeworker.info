<?php
session_start();
$isLoggedIn = isset($_SESSION['employer_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>FAQ - Homeworker Connect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="responsive.css?v=2">
  <link rel="stylesheet" href="styles.css">
  <script src="hamburger.js" defer></script>
  <style>
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
    }
    body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    main, .faq-container {
      flex: 1 0 auto;
    }
    footer {
      background-color: #0b5b81 !important;
      color: white;
      text-align: center;
      padding: 15px 0;
      flex-shrink: 0;
    }
    header {
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
      background-color: #0b5b81 !important;
    }
    nav {
      display: flex !important;
      align-items: center !important;
      margin-left: auto !important;
      position: relative !important;
    }
    .nav-links {
      display: flex !important;
      list-style: none !important;
      gap: 40px !important;
      align-items: center !important;
      justify-content: flex-end !important;
    }
    .register-btn {
      display: block;
      margin: 10px 0;
      padding: 12px;
      background-color: #f0f0f0;
      color: #00695c;
      text-decoration: none;
      font-weight: bold;
      border-radius: 8px;
      transition: background-color 0.3s;
    }
    .register-btn:hover {
      background-color: #e0f7f5;
    }
    .faq-container {
      max-width: 800px;
      margin: 60px auto;
      background: #fff;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 4px 16px rgba(24,123,136,0.08);
    }
    .faq-title {
      color: #197b88;
      font-size: 2.2rem;
      margin-bottom: 24px;
      text-align: center;
      font-weight: 700;
    }
    .faq-list {
      margin: 0;
      padding: 0;
      list-style: none;
    }
    .faq-q {
      font-weight: 600;
      color: #197b88;
      margin-top: 24px;
      margin-bottom: 8px;
      font-size: 1.15rem;
    }
    .faq-a {
      color: #333;
      margin-bottom: 16px;
      font-size: 1.05rem;
      line-height: 1.7;
    }
    @media (max-width: 600px) {
      .faq-container {
        padding: 18px 8px;
      }
      .faq-title {
        font-size: 1.4rem;
      }
    }
  </style>
</head>
<body>
<header>
  <div class="logo">
    <img src="home-worker-header.png" alt="Logo" style="height: 40px; margin-right: 10px;">
  </div>
  <nav class="main-nav">
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="about.php">About</a></li>
      <li><a href="contact.php">Contact Us</a></li>
      <li><a href="faq.php">FAQ</a></li>
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