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
  <link rel="stylesheet" href="responsive.css?v=2">
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
    main, .container {
      flex: 1 0 auto;
    }
    footer {
      background-color: rgb(24, 123, 136);
      color: white;
      text-align: center;
      padding: 15px 0;
      flex-shrink: 0;
    }
    header {
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
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
    .hero {
      position: relative;
      height: 100vh;
      background: url('houselpbg.jpg') center center / cover no-repeat;
      color: white;
    }
    .hero::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1;
    }
    .hero-content {
      position: relative;
      z-index: 2;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100%;
      text-align: center;
      padding: 0 20px;
    }
    .hero-content h1 {
      font-size: 3rem;
      font-weight: bold;
      margin-bottom: 20px;
      color: white;
    }
    .hero-content p {
      font-size: 1.4rem;
      line-height: 1.6;
      color: #f0f0f0;
      max-width: 700px;
      margin-bottom: 30px;
    }
    .btn {
      padding: 12px 24px;
      border-radius: 25px;
      background-color: #ff9800;
      color: white;
      font-weight: bold;
      text-decoration: none;
      margin-top: 10px;
    }
  </style>
</head>
<body>

<header>
  <div class="logo">
    <img src="logo.jpg" alt="Logo" style="height: 40px; margin-right: 10px;">
    Homeworker Connect
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
