<?php
session_start();
$isLoggedIn = isset($_SESSION['employer_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Us - Homeworker Connect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="responsive.css?v=2">
  <link rel="stylesheet" href="styles.css">
  <script src="hamburger.js" defer></script>
  <style>
    .contact-container {
      max-width: 700px;
      margin: 60px auto;
      background: #fff;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 4px 16px rgba(24,123,136,0.08);
    }
    .contact-title {
      color: #197b88;
      font-size: 2.2rem;
      margin-bottom: 24px;
      text-align: center;
      font-weight: 700;
    }
    .contact-details {
      margin-bottom: 32px;
      color: #333;
      font-size: 1.08rem;
      text-align: center;
    }
    .contact-form {
      display: flex;
      flex-direction: column;
      gap: 18px;
    }
    .contact-form label {
      font-weight: 600;
      color: #197b88;
      margin-bottom: 4px;
    }
    .contact-form input, .contact-form textarea {
      padding: 12px;
      border-radius: 8px;
      border: 1px solid #b2e0e6;
      font-size: 1rem;
      font-family: inherit;
      resize: vertical;
    }
    .contact-form textarea {
      min-height: 100px;
    }
    .contact-form button {
      background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%);
      color: #fff;
      padding: 14px 0;
      font-size: 1.1rem;
      font-weight: 700;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.2s;
    }
    .contact-form button:hover {
      background: linear-gradient(90deg, #17606e 0%, #1ec8c8 100%);
    }
    footer {
      background-color: #0b5b81 !important;
      color: white;
      text-align: center;
      padding: 15px 0;
      margin-top: 60px;
    }
    header {
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
      background-color: #0b5b81 !important;
    }
    @media (max-width: 600px) {
      .contact-container {
        padding: 18px 8px;
      }
      .contact-title {
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
<div class="contact-container">
  <div class="contact-title">Contact Us</div>
  <div class="contact-details">
    <div>Email: <a href="mailto:support@homeworkerconnect.co.ke">support@homeworker.info</a></div>
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
<footer>
  <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
</footer>
</body>
</html> 