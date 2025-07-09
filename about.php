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
  <link rel="stylesheet" href="responsive.css">
  <script src="hamburger.js" defer></script>
  <style>
    .container {
      max-width: 1000px;
      margin: 60px auto;
      background: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    h2 {
      color: rgb(24, 123, 136);
      margin-bottom: 15px;
    }

    p {
      line-height: 1.8;
      color: #333;
      margin-bottom: 20px;
    }

    ul {
      margin-top: 10px;
      padding-left: 20px;
    }

    .btn-home {
      display: inline-block;
      margin-top: 30px;
      padding: 12px 24px;
      background-color: rgb(24, 123, 136);
      color: white;
      text-decoration: none;
      border-radius: 8px;
    }

    footer {
      background-color: rgb(24, 123, 136);
      color: white;
      text-align: center;
      padding: 15px 0;
      margin-top: 60px;
    }

    @media (max-width: 768px) {
      .container {
        margin: 30px 15px;
        padding: 20px;
      }
    }
  </style>
</head>
<body>

<header>
  <div class="logo">
    <img src="logo.jpg" alt="Logo" style="height: 40px; margin-right: 10px;">
    Homeworker Connect
  </div>

  <nav>
    <div class="nav-toggle">☰</div>
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="about.php">About</a></li>
      <li><a href="contact.php">Contact Us</a></li>
      <li><a href="faq.php">FAQ</a></li>
      <?php if ($isLoggedIn): ?>
        <li><a href="employer_dashboard.php">Welcome, <?= htmlspecialchars($_SESSION['employer_name']) ?></a></li>
        <li><a href="logout.php">Logout</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>

<!-- About Page Content -->
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

<footer>
  <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
</footer>

</body>
</html>
