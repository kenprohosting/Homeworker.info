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

<section class="hero">
  <div class="hero-content">
    <h1>Find Validated Homeworkers Easily</h1>
    <p>Connecting employers with verified domestic workers across the world.</p>
    <p> +1000 verified homeworkers </p>

    <!-- Hero Action Card -->
    <div class="hero-action-card horizontal-card">
      <div class="hero-action-section">
        <button onclick="toggleDropdown()" class="btn pro-btn">
          Register <span class="chevron">▼</span>
        </button>
        <div id="registerDropdown" class="pro-dropdown">
          <a href="employer_register.php" class="pro-dropdown-link">Employer Register</a>
          <a href="agent_register.php" class="pro-dropdown-link">Agent Register</a>
        </div>
      </div>
      <div class="hero-divider-vertical"></div>
      <div class="hero-action-section">
        <button onclick="toggleLoginDropdown()" class="btn pro-btn">
          Login <span class="chevron">▼</span>
        </button>
        <div id="loginDropdown" class="pro-dropdown">
          <a href="employer_login.php" class="pro-dropdown-link">Employer Login</a>
          <a href="employee_login.php" class="pro-dropdown-link">Employee Login</a>
          <a href="agent_login.php" class="pro-dropdown-link">Agent Login</a>
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
  <!-- Top Footer -->
  <div class="footer-top">
    <div class="wrap">
      <div class="footer-grid">
        <!-- Homeworker For You -->
        <div class="footer-column">
          <h3>Homeworker For You</h3>
          <ul>
            <li><a href="employee_login.php">Employee Login</a></li>
            <li><a href="agent_register.php">Agent Application</a></li>
            <li><a href="agent_login.php">Agent Login</a></li>
            <li><a href="faq.php">FAQ</a></li>
          </ul>
        </div>

        <!-- Homeworker For Employer -->
        <div class="footer-column">
          <h3>Homeworker For Employer</h3>
          <ul>
            <li><a href="employer_register.php">Employer Register</a></li>
            <li><a href="employer_login.php">Employer Login</a></li>
            <li><a href="post_job.php">Post a Job</a></li>
          </ul>
        </div>

        <!-- Quick Links -->
        <div class="footer-column">
          <h3>Quick Links</h3>
          <ul>
            <li><a href="privacy_policy.php">Privacy Policy</a></li>
            <li><a href="agent_terms_and_conditions.php">Terms & Conditions</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="sitemap.xml">Sitemap</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Bottom Footer -->
  <div class="footer-bottom">
    <div class="footer-bottom-container">
      <div class="footer-links">
        <a href="privacy_policy.php">Privacy Policy</a>
        <span>|</span>
        <a href="agent_terms_and_conditions.php">Terms & Conditions</a>
        <span>|</span>
        <a href="sitemap.xml">Sitemap</a>
      </div>

      <p>&copy; <?= date("Y") ?> <a style="color: white;" href="https://www.kenpro.org/">KenPro</a>. All rights reserved.</p>

      <div class="footer-socials">
        <a href="https://facebook.com" target="_blank" aria-label="Facebook">
          <img src="/icons/Facebook.svg">
        </a>
        <a href="https://tiktok.com" target="_blank" aria-label="TikTok">
          <img src="/icons/Tiktok.svg">
        </a>
        <a href="https://wa.me/254712345678" target="_blank" aria-label="WhatsApp">
          <img src="/icons/Whatsapp.svg" >
        </a>
      </div>
    </div>
  </div>
</footer>



<script src="hamburger.js"></script>

</body>
</html>
