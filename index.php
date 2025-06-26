<?php
session_start();
$isLoggedIn = isset($_SESSION['employer_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Houselp Connect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="responsive.css">
  <script src="hamburger.js" defer></script>
  <style>
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

    footer {
      background-color: rgb(24, 123, 136);
      color: white;
      text-align: center;
      padding: 15px 0;
    }
  </style>
</head>
<body>

<header>
  <div class="logo">
    <img src="logo.jpg" alt="Logo" style="height: 40px; margin-right: 10px;">
    Houselp Connect
  </div>

  <nav>
    <div class="nav-toggle">☰</div>
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="about.php">About</a></li>

      <li class="dropdown">
        <a href="#">Login ▼</a>
        <ul class="dropdown-menu">
          <li><a href="employer_login.php">Employer</a></li>
          <li><a href="employee_login.php">Employee</a></li>
        </ul>
      </li>

      <li class="dropdown">
        <a href="#">Register ▼</a>
        <ul class="dropdown-menu">
          <li><a href="employer_register.php">Employer</a></li>
          <li><a href="employee_register.php">Employee</a></li>
        </ul>
      </li>

      <?php if ($isLoggedIn): ?>
        <li><a href="employer_dashboard.php">Welcome, <?= htmlspecialchars($_SESSION['employer_name']) ?></a></li>
        <li><a href="logout.php">Logout</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>

<section class="hero">
  <div class="hero-content">
    <h1>Find Trusted Househelps Easily</h1>
    <p>Connecting employers with verified domestic professionals across Kenya.</p>

    <!-- Get Started Dropdown Button -->
    <div style="text-align: center; position: relative; display: inline-block; margin-top: 30px;">
      <button onclick="toggleDropdown()" class="btn" style="background-color: rgb(24, 123, 136); color: white; padding: 14px 28px; font-size: 16px; border: none; border-radius: 8px; cursor: pointer;">
        Get Started ▼
      </button>

      <div id="registerDropdown" style="display: none; position: absolute; top: 100%; left: 50%; transform: translateX(-50%);
          background-color: #ffffff; box-shadow: 0 8px 16px rgba(0,0,0,0.2); border-radius: 12px; padding: 20px; z-index: 999; min-width: 260px; text-align: center;">
        <a href="employer_register.php" class="register-btn">👔 Register as Employer</a>
        <a href="employee_register.php" class="register-btn">🧹 Register as Employee</a>
      </div>
    </div>

    <script>
      function toggleDropdown() {
        const dropdown = document.getElementById('registerDropdown');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
      }

      // Close dropdown if clicked outside
      window.onclick = function(e) {
        if (!e.target.closest('.btn')) {
          const dd = document.getElementById('registerDropdown');
          if (dd && dd.style.display === 'block') {
            dd.style.display = 'none';
          }
        }
      }
    </script>
  </div>
</section>

<footer>
  <p>&copy; <?= date("Y") ?> Houselp Connect. All rights reserved.</p>
</footer>

</body>
</html>
