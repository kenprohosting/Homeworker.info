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
  <link rel="stylesheet" href="responsive.css?v=2">
  <script src="hamburger.js" defer></script>
  <style>
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
      background-color: #0b5b81 !important;
      color: white;
      text-align: center;
      padding: 15px 0;
    }
    .nav-btn {
      background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%);
      color: #fff !important;
      padding: 10px 22px;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      margin: 0 6px;
      transition: background 0.2s, color 0.2s, box-shadow 0.2s, border 0.2s;
      box-shadow: 0 2px 8px rgba(24,123,136,0.10);
      border: 2px solid #197b88;
      display: inline-block;
    }
    .nav-btn:hover, .nav-btn:focus {
      background: linear-gradient(90deg, #17606e 0%, #1ec8c8 100%);
      color: #ffd700 !important;
      box-shadow: 0 4px 16px rgba(24,123,136,0.16);
      outline: none;
      border-color: #125a66;
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
      <li><a class="nav-btn" href="index.php">Home</a></li>
      <li><a class="nav-btn" href="about.php">About</a></li>
      <li><a class="nav-btn" href="contact.php">Contact Us</a></li>
      <li><a class="nav-btn" href="faq.php">FAQ</a></li>
    </ul>
  </nav>
</header>

<section class="hero">
  <div class="hero-content">
    <h1>Find Trusted Homeworkers Easily</h1>
    <p>Connecting employers with verified domestic workers across the world.</p>

    <!-- Hero Action Card -->
    <div class="hero-action-card horizontal-card">
      <div class="hero-action-section">
        <button onclick="toggleDropdown()" class="btn pro-btn">
          Register <span class="chevron">▼</span>
        </button>
        <div id="registerDropdown" class="pro-dropdown">
          <a href="employer_register.php" class="pro-dropdown-link">Register as Employer</a>
          <a href="employee_register.php" class="pro-dropdown-link">Register as Employee</a>
        </div>
      </div>
      <div class="hero-divider-vertical"></div>
      <div class="hero-action-section">
        <button onclick="toggleLoginDropdown()" class="btn pro-btn">
          Login <span class="chevron">▼</span>
        </button>
        <div id="loginDropdown" class="pro-dropdown">
          <a href="employer_login.php" class="pro-dropdown-link">Login as Employer</a>
          <a href="employee_login.php" class="pro-dropdown-link">Login as Employee</a>
        </div>
      </div>
    </div>
    <style>
      .hero-action-card.horizontal-card {
        background: none;
        box-shadow: none;
        border-radius: 0;
        padding: 0;
        max-width: none;
        margin: 48px auto 0 auto;
        display: flex;
        flex-direction: row;
        align-items: stretch;
        justify-content: center;
        gap: 0;
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
        border: none;
      }
      .hero-action-card.horizontal-card .hero-action-section {
        flex: 1 1 0;
        min-width: 0;
        align-items: center;
        justify-content: center;
        display: flex;
        flex-direction: column;
        padding: 0 24px;
        height: 100%;
      }
      .hero-divider-vertical {
        width: 2px;
        background: linear-gradient(180deg, #e0e7ea 0%, #b2e0e6 100%);
        margin: 0;
        border-radius: 1px;
        height: auto;
        align-self: stretch;
      }
      .btn.pro-btn {
        background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%);
        color: #fff;
        padding: 18px 32px;
        width: 100%;
        min-width: 180px;
        font-size: 1.18rem;
        font-weight: 700;
        border: none;
        border-radius: 18px;
        cursor: pointer;
        box-shadow: 0 2px 12px rgba(24,123,136,0.10);
        transition: background 0.2s, box-shadow 0.2s, transform 0.1s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        outline: none;
        margin-bottom: 8px;
        min-height: 56px;
      }
      .btn.pro-btn:hover, .btn.pro-btn:focus {
        background: linear-gradient(90deg, #17606e 0%, #1ec8c8 100%);
        box-shadow: 0 4px 18px rgba(24,123,136,0.16);
        transform: translateY(-2px) scale(1.03);
      }
      .chevron {
        font-size: 1.1em;
        margin-left: 6px;
        transition: transform 0.2s;
      }
      .pro-login-label {
        color: #197b88;
        font-size: 1.08rem;
        margin-bottom: 12px;
        font-weight: 600;
        letter-spacing: 0.01em;
      }
      .pro-dropdown {
        display: none;
        position: absolute;
        left: 50%;
        top: 110%;
        transform: translateX(-50%) scaleY(0.95);
        background: #fff;
        box-shadow: 0 8px 32px rgba(24,123,136,0.13);
        border-radius: 16px;
        min-width: 240px;
        z-index: 1000;
        padding: 10px 0;
        text-align: left;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.22s cubic-bezier(.4,0,.2,1), transform 0.22s cubic-bezier(.4,0,.2,1);
      }
      .hero-action-section {
        position: relative;
      }
      .pro-dropdown.show {
        display: block;
        opacity: 1;
        pointer-events: auto;
        transform: translateX(-50%) scaleY(1);
      }
      .pro-dropdown-link {
        color: #197b88;
        background: none;
        padding: 15px 32px;
        border-radius: 10px;
        margin: 0;
        font-size: 1.08rem;
        display: block;
        text-align: left;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.18s, color 0.18s;
      }
      .pro-dropdown-link:hover, .pro-dropdown-link:focus {
        background: #eaf7fa;
        color: #125a66;
        outline: none;
      }
      @media (max-width: 900px) {
        .hero-action-card.horizontal-card {
          flex-direction: column;
          max-width: 98vw;
          padding: 18px 4vw 18px 4vw;
        }
        .hero-action-card.horizontal-card .hero-action-section {
          padding: 0 0 24px 0;
        }
        .hero-divider-vertical {
          width: 80%;
          height: 2px;
          margin: 24px auto;
          background: linear-gradient(90deg, #e0e7ea 0%, #b2e0e6 100%);
        }
        .btn.pro-btn {
          font-size: 1rem;
          padding: 15px 0;
        }
        .pro-dropdown {
          min-width: 90vw;
        }
      }
    </style>

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
