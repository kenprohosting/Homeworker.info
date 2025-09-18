<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 26 Jul 2099 05:00:00 GMT");
header("Pragma: no-cache");
$isLoggedIn = isset($_SESSION['employer_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Homeworker Connect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="favicon.png" rel="icon" type="image/png" />


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
      <button onclick="toggleJobsDropdown()" class="nav-btn pro-btn">
        Jobs <span class="chevron">▼</span>
      </button>
      <div id="jobsDropdown" class="pro-dropdown">
        <a href="resources.php" class="pro-dropdown-link">Freelancer Jobs</a>
        <a href="resources.php" class="pro-dropdown-link">Househelp Jobs</a>
        <a href="post_job.php" class="pro-dropdown-link">Post a Job</a>
        
      </div>
      
      <li><a class="nav-btn" href="resources.php">Resources</a></li>
      <li><a class="nav-btn" href="contact.php">Contact Us</a></li>
      <li><a class="nav-btn" href="faq.php">FAQ</a></li>
    </ul>
  </nav>
    <script scr="scripts.js"></script>
    <script src="hamburger.js"></script>

 </div>
</header>