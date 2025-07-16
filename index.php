<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
$isLoggedIn = isset($_SESSION['employer_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Homeworker Connect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">


  <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
  <div class="logo">
    <img src="bghse.png" alt="Logo" style="height: 40px; margin-right: 10px;">
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

<section class="hero">
  <div class="hero-content">
    <h1>Find Validated Homeworkers Easily</h1>
    <p>Connecting employers with verified domestic workers across the world.</p>

    <!-- Hero Action Card -->
    <div class="hero-action-card horizontal-card">
      <div class="hero-action-section">
        <button onclick="toggleDropdown()" class="btn pro-btn">
          Register <span class="chevron">▼</span>
        </button>
        <div id="registerDropdown" class="pro-dropdown">
          <a href="employer_register.php" class="pro-dropdown-link">Register as Employer</a>
          <a href="agent_register.php" class="pro-dropdown-link">Register as Agent</a>
        </div>
      </div>
      <div class="hero-divider-vertical"></div>
      <div class="hero-action-section">
        <button onclick="toggleLoginDropdown()" class="btn pro-btn">
          Login <span class="chevron">▼</span>
        </button>
        <div id="loginDropdown" class="pro-dropdown">
          <a href="employer_login.php" class="pro-dropdown-link">Login as Employer</a>
          <a href="employee_access.php" class="pro-dropdown-link">Login as Employee</a>
          <a href="agent_login.php" class="pro-dropdown-link">Login as Agent</a>
        </div>
      </div>
    </div>


    <script>
      function toggleDropdown() {
        const dropdown = document.getElementById('registerDropdown');
        dropdown.classList.toggle('show');
      }
      function toggleLoginDropdown() {
        const dropdown = document.getElementById('loginDropdown');
        dropdown.classList.toggle('show');
      }
      // Close dropdowns if clicked outside or on Escape
      window.addEventListener('click', function(e) {
        if (!e.target.closest('.pro-btn')) {
          const dd = document.getElementById('registerDropdown');
          if (dd && dd.classList.contains('show')) {
            dd.classList.remove('show');
          }
        }
        if (!e.target.closest('.pro-btn')) {
          const ld = document.getElementById('loginDropdown');
          if (ld && ld.classList.contains('show')) {
            ld.classList.remove('show');
          }
        }
      });
      window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          const dd = document.getElementById('registerDropdown');
          if (dd) dd.classList.remove('show');
          const ld = document.getElementById('loginDropdown');
          if (ld) ld.classList.remove('show');
        }
      });
    </script>
  </div>
</section>

<footer>
  <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
</footer>

</body>
</html>
