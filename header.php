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
  <style>
    /* Socials Bar Styles */
    .socials-bar {
      background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%);
      padding: 8px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 14px;
    }
    
    .socials-left {
      display: flex;
      align-items: center;
      font-weight: 500;
      color: #495057;
    }
    
    .socials-center {
      display: flex;
      align-items: center;
      font-size: 16px;
      font-weight: 600;
      color: #2c3e50;
    }
    
    .socials-right {
      display: flex;
      align-items: center;
    }
    
    .header-socials {
      display: flex;
      gap: 15px;
      margin-left: 15px;
    }
    
    .header-socials a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 24px;
      height: 24px;
      transition: transform 0.2s;
    }
    
    .header-socials a:hover {
      transform: scale(1.1);
    }
    
    .header-socials img {
      width: 20px;
      height: 20px;
    }
    
    .email-link {
      color: #031f32ff;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    
    .email-link:hover {
      text-decoration: underline;
    }
    
    .email-icon {
      width: 16px;
      height: 16px;
    }
    
    .region-info {
      margin-left: 10px;
      color: #5a6c7d;
      font-size: 14px;
    }
    
    /* Mobile Styles */
    @media (max-width: 768px) {
      .socials-bar {
        padding: 10px 15px;
        justify-content: center;
      }
      
      .socials-left,
      .socials-center,
      .socials-right {
        display: none;
      }
      
      .mobile-socials {
        display: flex;
        justify-content: center;
        width: 100%;
      }
      
      .mobile-socials .header-socials {
        margin: 0;
        gap: 20px;
      }
      
      .mobile-socials .header-socials a {
        width: 28px;
        height: 28px;
      }
      
      .mobile-socials .header-socials img {
        width: 24px;
        height: 24px;
      }
    }
    
    /* Hide mobile socials on desktop */
    @media (min-width: 769px) {
      .mobile-socials {
        display: none;
      }
    }
  </style>
</head>
<body>

<!-- Socials Bar -->
<div class="socials-bar">
  <div class="socials-left" id="datetime">
    <!-- Date and time will be inserted here by JavaScript -->
  </div>
  
  <div class="socials-center" id="timeRegion">
    <!-- Time and region will be inserted here by JavaScript -->
  </div>
  
  <div class="socials-right">
    <a href="mailto:support@homeworker.info" class="email-link">
      <svg class="email-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
        <polyline points="22,6 12,13 2,6"></polyline>
      </svg>
      support@homeworker.info
    </a>
    
    <div class="header-socials">
      <a href="https://facebook.com" target="_blank" aria-label="Facebook">
        <img src="/icons/Facebook.svg" alt="Facebook">
      </a>
      <a href="https://tiktok.com" target="_blank" aria-label="TikTok">
        <img src="/icons/Tiktok.svg" alt="TikTok">
      </a>
      <a href="https://wa.me/254712345678" target="_blank" aria-label="WhatsApp">
        <img src="/icons/Whatsapp.svg" alt="WhatsApp">
      </a>
    </div>
  </div>
  
  <!-- Mobile-only socials -->
  <div class="mobile-socials">
    <div class="header-socials">
      <a href="https://facebook.com" target="_blank" aria-label="Facebook">
        <img src="/icons/Facebook.svg" alt="Facebook">
      </a>
      <a href="https://tiktok.com" target="_blank" aria-label="TikTok">
        <img src="/icons/Tiktok.svg" alt="TikTok">
      </a>
      <a href="https://wa.me/254712345678" target="_blank" aria-label="WhatsApp">
        <img src="/icons/Whatsapp.svg" alt="WhatsApp">
      </a>
    </div>
  </div>
</div>

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
    <div class="jobs-menu">
      <button onclick="toggleJobsDropdown()" class="nav-btn pro-btn">
        Jobs <span class="chevron">▼</span>
      </button>
      <div id="jobsDropdown" class="pro-dropdown">
        <a href="resources.php" class="pro-dropdown-link">Freelancer Jobs</a>
        <a href="resources.php" class="pro-dropdown-link">Househelp Jobs</a>
        <a href="post_job.php" class="pro-dropdown-link">Post a Job</a>
        
      </div>
    </div>
      
      <!--<li><a class="nav-btn" href="resources.php">Resources</a></li> -->
      <li><a class="nav-btn" href="faq.php">FAQ</a></li>
      <li><a class="nav-btn" href="contact.php">Contact Us</a></li>
      
    </ul>
  </nav>
    <script scr="scripts.js"></script>
    <script src="hamburger.js"></script>

 </div>
</header>

<script>
// Function to get region information
function getRegionInfo() {
  const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
  const now = new Date();
  
  // Try to get country from timezone
  let continent = 'Unknown';
  let country = 'Unknown';
  
  // Common timezone to continent/country mappings
  const timezoneMap = {
    'America/New_York': { continent: 'North America', country: 'USA' },
    'America/Chicago': { continent: 'North America', country: 'USA' },
    'America/Denver': { continent: 'North America', country: 'USA' },
    'America/Los_Angeles': { continent: 'North America', country: 'USA' },
    'America/Toronto': { continent: 'North America', country: 'Canada' },
    'America/Vancouver': { continent: 'North America', country: 'Canada' },
    'America/Mexico_City': { continent: 'North America', country: 'Mexico' },
    'America/Sao_Paulo': { continent: 'South America', country: 'Brazil' },
    'America/Buenos_Aires': { continent: 'South America', country: 'Argentina' },
    'Europe/London': { continent: 'Europe', country: 'UK' },
    'Europe/Paris': { continent: 'Europe', country: 'France' },
    'Europe/Berlin': { continent: 'Europe', country: 'Germany' },
    'Europe/Rome': { continent: 'Europe', country: 'Italy' },
    'Europe/Madrid': { continent: 'Europe', country: 'Spain' },
    'Europe/Amsterdam': { continent: 'Europe', country: 'Netherlands' },
    'Europe/Brussels': { continent: 'Europe', country: 'Belgium' },
    'Europe/Moscow': { continent: 'Europe', country: 'Russia' },
    'Africa/Cairo': { continent: 'Africa', country: 'Egypt' },
    'Africa/Lagos': { continent: 'Africa', country: 'Nigeria' },
    'Africa/Johannesburg': { continent: 'Africa', country: 'South Africa' },
    'Africa/Nairobi': { continent: 'Africa', country: 'Kenya' },
    'Asia/Dubai': { continent: 'Asia', country: 'UAE' },
    'Asia/Kolkata': { continent: 'Asia', country: 'India' },
    'Asia/Shanghai': { continent: 'Asia', country: 'China' },
    'Asia/Tokyo': { continent: 'Asia', country: 'Japan' },
    'Asia/Seoul': { continent: 'Asia', country: 'South Korea' },
    'Asia/Singapore': { continent: 'Asia', country: 'Singapore' },
    'Asia/Hong_Kong': { continent: 'Asia', country: 'Hong Kong' },
    'Asia/Bangkok': { continent: 'Asia', country: 'Thailand' },
    'Asia/Jakarta': { continent: 'Asia', country: 'Indonesia' },
    'Asia/Manila': { continent: 'Asia', country: 'Philippines' },
    'Australia/Sydney': { continent: 'Oceania', country: 'Australia' },
    'Australia/Melbourne': { continent: 'Oceania', country: 'Australia' },
    'Pacific/Auckland': { continent: 'Oceania', country: 'New Zealand' }
  };
  
  // Check if timezone is in our map
  if (timezoneMap[timezone]) {
    continent = timezoneMap[timezone].continent;
    country = timezoneMap[timezone].country;
  } else {
    // Try to extract continent from timezone name
    if (timezone.includes('America/')) {
      continent = 'North America';
      if (timezone.includes('Argentina') || timezone.includes('Brazil') || timezone.includes('Chile') || timezone.includes('Peru')) {
        continent = 'South America';
      }
    } else if (timezone.includes('Europe/')) {
      continent = 'Europe';
    } else if (timezone.includes('Africa/')) {
      continent = 'Africa';
    } else if (timezone.includes('Asia/')) {
      continent = 'Asia';
    } else if (timezone.includes('Australia/') || timezone.includes('Pacific/')) {
      continent = 'Oceania';
    }
  }
  
  return { continent, country };
}

// Function to update date and time
function updateDateTime() {
  const now = new Date();
  
  // Format date: Day of the week / Month name / Year
  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  
  const dayName = days[now.getDay()];
  const monthName = months[now.getMonth()];
  const year = now.getFullYear();
  
  // Format time: 24hr format hr/min/sec
  const hours = String(now.getHours()).padStart(2, '0');
  const minutes = String(now.getMinutes()).padStart(2, '0');
  const seconds = String(now.getSeconds()).padStart(2, '0');
  
  // Get region information
  const region = getRegionInfo();
  
  // Update the datetime element (left side)
  const datetimeElement = document.getElementById('datetime');
  if (datetimeElement) {
    datetimeElement.innerHTML = `<strong>${dayName} / ${monthName} / ${year}</strong>`;
  }
  
  // Update the time and region element (center)
  const timeRegionElement = document.getElementById('timeRegion');
  if (timeRegionElement) {
    timeRegionElement.innerHTML = `<strong>${hours}:${minutes}:${seconds}</strong><span class="region-info"> | ${region.continent} - ${region.country}</span>`;
  }
}

// Update date and time immediately and then every second
updateDateTime();
setInterval(updateDateTime, 1000);
</script>