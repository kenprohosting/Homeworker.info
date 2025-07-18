<?php
session_start();
if (!isset($_SESSION['employer_id'])) {
    header("Location:employer_login.php");
    exit();
}
require_once('db_connect.php');

$message = '';

// Handle unbook action from dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'unbook') {
    $booking_id = $_POST['booking_id'];
    $stmt = $conn->prepare("UPDATE bookings SET Status = 'cancelled' WHERE ID = ? AND Homeowner_ID = ?");
    $stmt->execute([$booking_id, $_SESSION['employer_id']]);
    $message = "<div style='background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;'>🔄 Booking cancelled successfully!</div>";
}

// Fetch employees with filters
$filter_sql = "SELECT * FROM employees WHERE 1";
$params = [];

if (!empty($_GET['skill'])) {
    $filter_sql .= " AND Skills LIKE ?";
    $params[] = '%' . $_GET['skill'] . '%';
}
if (!empty($_GET['country'])) {
    $filter_sql .= " AND country LIKE ?";
    $params[] = '%' . $_GET['country'] . '%';
}
if (!empty($_GET['county_province'])) {
    $filter_sql .= " AND county_province LIKE ?";
    $params[] = '%' . $_GET['county_province'] . '%';
}
if (!empty($_GET['gender'])) {
    $filter_sql .= " AND Gender = ?";
    $params[] = $_GET['gender'];
}
if (!empty($_GET['residence'])) {
    $filter_sql .= " AND residence_type = ?";
    $params[] = $_GET['residence'];
}

// Modify query to include booking status
$filter_sql = "SELECT e.*, 
               b.Status as booking_status,
               b.ID as booking_id
               FROM employees e 
               LEFT JOIN bookings b ON e.ID = b.Employee_ID AND b.Homeowner_ID = ? AND b.Status = 'pending'
               WHERE 1";

// Add the employer ID as the first parameter
$params = [$_SESSION['employer_id']];

// Re-add the filter conditions
if (!empty($_GET['skill'])) {
    $filter_sql .= " AND e.Skills LIKE ?";
    $params[] = '%' . $_GET['skill'] . '%';
}
if (!empty($_GET['country'])) {
    $filter_sql .= " AND e.country LIKE ?";
    $params[] = '%' . $_GET['country'] . '%';
}
if (!empty($_GET['county_province'])) {
    $filter_sql .= " AND e.county_province LIKE ?";
    $params[] = '%' . $_GET['county_province'] . '%';
}
if (!empty($_GET['gender'])) {
    $filter_sql .= " AND e.Gender = ?";
    $params[] = $_GET['gender'];
}
if (!empty($_GET['residence'])) {
    $filter_sql .= " AND e.residence_type = ?";
    $params[] = $_GET['residence'];
}

$stmt = $conn->prepare($filter_sql);
$stmt->execute($params);
$employee = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch employer's bookings
$stmt2 = $conn->prepare("
    SELECT b.*, emp.Name AS employee_name 
    FROM bookings b 
    JOIN employees emp ON b.Employee_ID = emp.ID 
    WHERE b.Homeowner_ID = ?
    ORDER BY b.Booking_date DESC
");
$stmt2->execute([$_SESSION['employer_id']]);
$bookings = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
  <title>Employer Dashboard - Homeworker Connect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Main CSS disabled to remove black borders - essential styles added inline -->
  <style>
    /* Completely override body styling */
    body {
      border: none !important;
      outline: none !important;
      margin: 0 !important;
      padding: 0 !important;
      min-height: 100vh !important;
      display: flex !important;
      flex-direction: column !important;
      background-color: #f8f9fa !important;
    }
    
    html {
      border: none !important;
      outline: none !important;
      margin: 0 !important;
      padding: 0 !important;
    }
    
    /* Remove any viewport or container borders */
    * {
      box-sizing: border-box;
    }
    
    /* Override main CSS borders */
    .container, main, section, div {
      border: none !important;
    }
    
    /* Remove header borders */
    header {
      border: none !important;
    }
    
    /* Remove footer borders */
    footer {
      border: none !important;
    }
    
    /* Add essential header and navigation styling */
    header {
      background-color: #0A4A70 !important;
      color: white !important;
      padding: 15px 20px !important;
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
      flex-wrap: wrap !important;
      position: relative !important;
    }
    
    .logo {
      font-size: 1.5rem !important;
      font-weight: bold !important;
      display: flex !important;
      align-items: center !important;
    }
    
    .main-nav {
      margin-left: auto !important;
      display: flex !important;
      align-items: center !important;
    }
    
    .nav-btn {
      background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%) !important;
      color: #fff !important;
      padding: 10px 22px !important;
      border-radius: 8px !important;
      font-weight: 600 !important;
      text-decoration: none !important;
      margin: 0 6px !important;
      transition: background 0.2s, color 0.2s, box-shadow 0.2s, border 0.2s !important;
      box-shadow: 0 2px 8px rgba(24,123,136,0.10) !important;
      border: 2px solid #197b88 !important;
      display: inline-block !important;
      cursor: pointer !important;
    }
    
    .nav-btn:hover, .nav-btn:focus {
      background: linear-gradient(90deg, #17606e 0%, #1ec8c8 100%) !important;
      color: #ffd700 !important;
      box-shadow: 0 4px 16px rgba(24,123,136,0.16) !important;
      outline: none !important;
      border-color: #125a66 !important;
    }
    
    /* Footer styling */
    footer {
      background-color: #0A4A70 !important;
      color: white !important;
      text-align: center !important;
      padding: 15px 0 !important;
      margin-top: auto !important;
    }
    
    /* Force remove any viewport or page borders */
    html, body {
      border: 0 !important;
      outline: 0 !important;
      box-shadow: none !important;
    }
    
    /* Override any main CSS that might add borders */
    body::before, body::after {
      display: none !important;
    }
    
    /* Remove any potential wrapper borders */
    .wrapper, .page-wrapper, .main-wrapper {
      border: none !important;
    }
    
    /* Force remove any viewport or page borders */
    html, body {
      border: 0 !important;
      outline: 0 !important;
      box-shadow: none !important;
    }
    
    /* Override any main CSS that might add borders */
    body::before, body::after {
      display: none !important;
    }
    
    /* Remove any potential wrapper borders */
    .wrapper, .page-wrapper, .main-wrapper {
      border: none !important;
    }
    
    /* Country dropdown styling */
    #countryList {
      list-style: none;
      background: white;
      position: absolute;
      z-index: 999;
      border: 1px solid #ddd;
      max-height: 150px;
      overflow-y: auto;
      display: none;
      width: 100%;
      padding: 0;
      margin: 0;
      border-radius: 5px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      top: 100%;
      left: 0;
    }
    #countryList li {
      padding: 8px 10px;
      cursor: pointer;
      border-bottom: 1px solid #f0f0f0;
      transition: background-color 0.2s;
    }
    #countryList li:hover {
      background: #f0f0f0;
    }
    #countryList li:last-child {
      border-bottom: none;
    }
    
    /* Remove any inherited borders */
    .form-container * {
      border: none !important;
    }
    
    /* Restore necessary borders only where needed */
    .filter-form input, .filter-form select {
      border: 1px solid #e0e0e0 !important;
    }
    
    .card img {
      border: none !important;
    }
    
    /* Main content area styling */
    .form-container { 
      padding: 30px;
      width: 100vw;
      max-width: none;
      margin-left: calc(50% - 50vw);
      box-sizing: border-box;
      background: #f8f9fa;
      border: none !important;
      flex: 1;
    }
    
    /* Page title styling */
    h2 {
      color: #197b88;
      font-size: 2rem;
      margin-bottom: 30px;
      text-align: center;
      font-weight: 600;
    }
    
    /* Search form styling */
    .filter-form {
      display: flex;
      flex-wrap: nowrap;
      gap: 10px;
      align-items: center;
      margin-bottom: 30px;
      padding: 0;
    }
    
    .filter-form input, .filter-form select {
      padding: 10px 14px;
      border: 1px solid #ddd;
      border-radius: 5px;
      flex: 0 1 auto;
      width: 180px;
      font-size: 14px;
      outline: none;
      font-family: 'Segoe UI', sans-serif;
      height: 40px;
      box-sizing: border-box;
    }
    
    .filter-form input:focus, .filter-form select:focus {
      border-color: #197b88;
      box-shadow: 0 0 0 2px rgba(25, 123, 136, 0.1);
    }
    
    .filter-form button {
      padding: 10px 20px;
      background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%);
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 600;
      font-size: 14px;
      height: 40px;
      width: 100px;
      flex-shrink: 0;
      transition: all 0.3s ease;
      font-family: 'Segoe UI', sans-serif;
      box-sizing: border-box;
    }
    
    .filter-form button:hover {
      background: linear-gradient(90deg, #17606e 0%, #1ec8c8 100%);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(24,123,136,0.3);
    }
    
    /* Employee cards grid */
    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
      gap: 30px;
      margin-top: 30px;
      padding: 0 20px;
    }
    
    /* Individual employee card */
    .card {
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: none;
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    /* Profile image styling */
    .card img {
      width: 120px;
      height: 120px;
      border-radius: 8px;
      object-fit: cover;
      margin-bottom: 20px;
      border: none !important;
      box-shadow: 0 4px 15px rgba(24,123,136,0.2);
    }
    
    /* Card typography */
    .card h3 {
      font-size: 1.4rem;
      color: #197b88;
      margin-bottom: 15px;
      font-weight: 600;
      font-family: 'Segoe UI', sans-serif;
    }
    
    .card p {
      font-size: 1rem;
      margin: 10px 0;
      line-height: 1.5;
      color: #555;
      font-family: 'Segoe UI', sans-serif;
    }
    
    .card p strong {
      color: #333;
      font-weight: 600;
    }
    
    /* Book Now button */
    .card .btn {
      margin-top: 20px;
      padding: 12px 25px;
      font-size: 1.1rem;
      font-weight: 600;
      background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%);
      border: none;
      border-radius: 25px;
      transition: all 0.3s ease;
      text-decoration: none;
      color: white;
      display: inline-block;
      font-family: 'Segoe UI', sans-serif;
    }
    
    .card .btn:hover {
      background: linear-gradient(90deg, #17606e 0%, #1ec8c8 100%);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(24,123,136,0.3);
      color: white;
      text-decoration: none;
    }
    
    /* Disabled button styling */
    .card .btn-disabled {
      margin-top: 20px;
      padding: 12px 25px;
      font-size: 1.1rem;
      font-weight: 600;
      background: #6c757d;
      border: none;
      border-radius: 25px;
      color: white;
      display: inline-block;
      font-family: 'Segoe UI', sans-serif;
      cursor: not-allowed;
      opacity: 0.7;
    }
    
    .card .btn-disabled:hover {
      background: #6c757d;
      transform: none;
      box-shadow: none;
    }
    
    /* User greeting */
    .user-greeting {
      color: white;
      font-weight: 500;
      padding: 10px 16px;
      font-family: 'Segoe UI', sans-serif;
    }
    
    /* Ensure navigation buttons remain visible and functional */
    .nav-links {
      display: flex !important;
      list-style: none !important;
      gap: 20px;
      align-items: center;
      margin: 0;
      padding: 0;
    }
    
    .nav-btn {
      display: inline-block !important;
      visibility: visible !important;
      opacity: 1 !important;
    }
    
    /* Fix for mobile responsiveness */
    @media (max-width: 768px) {
      .nav-links {
        flex-direction: column;
        width: 100%;
        gap: 10px;
      }
      
      .nav-btn {
        width: 100%;
        text-align: center;
        margin: 0;
        padding: 12px 20px;
      }
    }
  </style>
</head>
<body>

<header>
  <div class="logo">
    <img src="bghse.png" alt="Logo" style="height: 40px;">
  </div>
  <nav class="main-nav">
    <ul class="nav-links">
      <li><span class="user-greeting">Hello, <?= htmlspecialchars($_SESSION['employer_name']) ?></span></li>
      <li><a class="nav-btn" href="post_job.php">Post Job</a></li>
      <li><a class="nav-btn" href="manage_jobs.php">My Jobs</a></li>
      <li><a class="nav-btn" href="employer_bookings.php">My Bookings</a></li>
      <li><a class="nav-btn" href="employer_logout.php">Logout</a></li>
    </ul>
  </nav>
</header>

<div class="form-container">
  <h2>Find a Homewoker</h2>
  
  <?= $message ?>

  <form method="GET" class="filter-form">
    <input type="text" name="skill" placeholder="Job Title (e.g. Driver)">
    <div style="position:relative;">
      <input type="text" id="countryInput" name="country" placeholder="Country" autocomplete="off" required>
      <ul id="countryList" class="country-dropdown"></ul>
    </div>
    <input type="text" name="county_province" placeholder="County or Province">
    <select name="gender">
      <option value="">Gender</option>
      <option>Male</option>
      <option>Female</option>
    </select>
    <select name="residence">
      <option value="">Residence</option>
      <option value="urban">Urban</option>
      <option value="rural">Rural</option>
    </select>
    <button type="submit" class="btn search-btn">Search</button>
  </form>



  <!-- Employee Cards -->
  <div class="card-grid">
    <?php foreach ($employee as $emp): ?>
      <div class="card">
        <?php
          $profilePic = $emp['Profile_pic'] ?? '';
          if (!empty($profilePic) && file_exists($profilePic)) {
              $imgSrc = htmlspecialchars($profilePic);
          } else {
              $imgSrc = 'placeholder-profile.svg';
          }
        ?>
        <img src="<?= $imgSrc ?>" alt="Profile Picture">
        <h3><?= htmlspecialchars($emp['name'] ?? 'N/A') ?> (<?= $emp['age'] ?? 'N/A' ?>)</h3>
        <p><strong>Job Title:</strong> <?= htmlspecialchars($emp['skills'] ?? 'N/A') ?></p>
        <p><strong>Country:</strong> <?= htmlspecialchars($emp['country'] ?? 'N/A') ?></p>
        <p><strong>County/Province:</strong> <?= htmlspecialchars($emp['county_province'] ?? 'N/A') ?></p>
        <p><strong>Language:</strong> <?= htmlspecialchars($emp['language'] ?? 'N/A') ?></p>
        <p><strong>Education:</strong> <?= htmlspecialchars($emp['education_level'] ?? 'N/A') ?></p>
        <?php if ($emp['booking_status'] === 'pending'): ?>
          <div style="margin-top: 20px;">
            <p style="color: #00695c; font-weight: bold; margin-bottom: 10px;">✅ Booked</p>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="booking_id" value="<?= $emp['booking_id'] ?>">
              <button type="submit" name="action" value="unbook" 
                      onclick="return confirm('Are you sure you want to cancel this booking?')"
                      style="background-color: #d32f2f; color: white; border: none; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-size: 0.9rem; transition: all 0.3s ease;">
                Cancel Booking
              </button>
            </form>
          </div>
        <?php else: ?>
          <a href="employer_booking.php?eid=<?= $emp['id'] ?? '' ?>" class="btn">Book Now</a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- JS for Country Flags -->
<script>

  // Country List Script
  const countryInput = document.getElementById("countryInput");
  const countryList = document.getElementById("countryList");
  const countries = [
      { name: "Afghanistan", flag: "🇦🇫" },
  { name: "Albania", flag: "🇦🇱" },
  { name: "Algeria", flag: "🇩🇿" },
  { name: "Andorra", flag: "🇦🇩" },
  { name: "Angola", flag: "🇦🇴" },
  { name: "Antigua and Barbuda", flag: "🇦🇬" },
  { name: "Argentina", flag: "🇦🇷" },
  { name: "Armenia", flag: "🇦🇲" },
  { name: "Australia", flag: "🇦🇺" },
  { name: "Austria", flag: "🇦🇹" },
  { name: "Azerbaijan", flag: "🇦🇿" },
  { name: "Bahamas", flag: "🇧🇸" },
  { name: "Bahrain", flag: "🇧🇭" },
  { name: "Bangladesh", flag: "🇧🇩" },
  { name: "Barbados", flag: "🇧🇧" },
  { name: "Belarus", flag: "🇧🇾" },
  { name: "Belgium", flag: "🇧🇪" },
  { name: "Belize", flag: "🇧🇿" },
  { name: "Benin", flag: "🇧🇯" },
  { name: "Bhutan", flag: "🇧🇹" },
  { name: "Bolivia", flag: "🇧🇴" },
  { name: "Bosnia and Herzegovina", flag: "🇧🇦" },
  { name: "Botswana", flag: "🇧🇼" },
  { name: "Brazil", flag: "🇧🇷" },
  { name: "Brunei", flag: "🇧🇳" },
  { name: "Bulgaria", flag: "🇧🇬" },
  { name: "Burkina Faso", flag: "🇧🇫" },
  { name: "Burundi", flag: "🇧🇮" },
  { name: "Cabo Verde", flag: "🇨🇻" },
  { name: "Cambodia", flag: "🇰🇭" },
  { name: "Cameroon", flag: "🇨🇲" },
  { name: "Canada", flag: "🇨🇦" },
  { name: "Central African Republic", flag: "🇨🇫" },
  { name: "Chad", flag: "🇹🇩" },
  { name: "Chile", flag: "🇨🇱" },
  { name: "China", flag: "🇨🇳" },
  { name: "Colombia", flag: "🇨🇴" },
  { name: "Comoros", flag: "🇰🇲" },
  { name: "Congo (Brazzaville)", flag: "🇨🇬" },
  { name: "Congo (Kinshasa)", flag: "🇨🇩" },
  { name: "Costa Rica", flag: "🇨🇷" },
  { name: "Croatia", flag: "🇭🇷" },
  { name: "Cuba", flag: "🇨🇺" },
  { name: "Cyprus", flag: "🇨🇾" },
  { name: "Czech Republic", flag: "🇨🇿" },
  { name: "Denmark", flag: "🇩🇰" },
  { name: "Djibouti", flag: "🇩🇯" },
  { name: "Dominica", flag: "🇩🇲" },
  { name: "Dominican Republic", flag: "🇩🇴" },
  { name: "Ecuador", flag: "🇪🇨" },
  { name: "Egypt", flag: "🇪🇬" },
  { name: "El Salvador", flag: "🇸🇻" },
  { name: "Equatorial Guinea", flag: "🇬🇶" },
  { name: "Eritrea", flag: "🇪🇷" },
  { name: "Estonia", flag: "🇪🇪" },
  { name: "Eswatini", flag: "🇸🇿" },
  { name: "Ethiopia", flag: "🇪🇹" },
  { name: "Fiji", flag: "🇫🇯" },
  { name: "Finland", flag: "🇫🇮" },
  { name: "France", flag: "🇫🇷" },
  { name: "Gabon", flag: "🇬🇦" },
  { name: "Gambia", flag: "🇬🇲" },
  { name: "Georgia", flag: "🇬🇪" },
  { name: "Germany", flag: "🇩🇪" },
  { name: "Ghana", flag: "🇬🇭" },
  { name: "Greece", flag: "🇬🇷" },
  { name: "Grenada", flag: "🇬🇩" },
  { name: "Guatemala", flag: "🇬🇹" },
  { name: "Guinea", flag: "🇬🇳" },
  { name: "Guinea-Bissau", flag: "🇬🇼" },
  { name: "Guyana", flag: "🇬🇾" },
  { name: "Haiti", flag: "🇭🇹" },
  { name: "Honduras", flag: "🇭🇳" },
  { name: "Hungary", flag: "🇭🇺" },
  { name: "Iceland", flag: "🇮🇸" },
  { name: "India", flag: "🇮🇳" },
  { name: "Indonesia", flag: "🇮🇩" },
  { name: "Iran", flag: "🇮🇷" },
  { name: "Iraq", flag: "🇮🇶" },
  { name: "Ireland", flag: "🇮🇪" },
  { name: "Israel", flag: "🇮🇱" },
  { name: "Italy", flag: "🇮🇹" },
  { name: "Jamaica", flag: "🇯🇲" },
  { name: "Japan", flag: "🇯🇵" },
  { name: "Jordan", flag: "🇯🇴" },
  { name: "Kazakhstan", flag: "🇰🇿" },
  { name: "Kenya", flag: "🇰🇲" },
  { name: "Kiribati", flag: "🇰🇮" },
  { name: "Kuwait", flag: "🇰🇼" },
  { name: "Kyrgyzstan", flag: "🇰🇬" },
  { name: "Laos", flag: "🇱🇦" },
  { name: "Latvia", flag: "🇱🇻" },
  { name: "Lebanon", flag: "🇱🇧" },
  { name: "Lesotho", flag: "🇱🇸" },
  { name: "Liberia", flag: "🇱🇷" },
  { name: "Libya", flag: "🇱🇾" },
  { name: "Liechtenstein", flag: "🇱🇮" },
  { name: "Lithuania", flag: "🇱🇹" },
  { name: "Luxembourg", flag: "🇱🇺" },
  { name: "Madagascar", flag: "🇲🇬" },
  { name: "Malawi", flag: "🇲🇼" },
  { name: "Malaysia", flag: "🇲🇾" },
  { name: "Maldives", flag: "🇲🇻" },
  { name: "Mali", flag: "🇲🇱" },
  { name: "Malta", flag: "🇲🇹" },
  { name: "Marshall Islands", flag: "🇲🇭" },
  { name: "Mauritania", flag: "🇲🇷" },
  { name: "Mauritius", flag: "🇲🇺" },
  { name: "Mexico", flag: "🇲🇽" },
  { name: "Micronesia", flag: "🇫🇲" },
  { name: "Moldova", flag: "🇲🇩" },
  { name: "Monaco", flag: "🇲🇨" },
  { name: "Mongolia", flag: "🇲🇳" },
  { name: "Montenegro", flag: "🇲🇪" },
  { name: "Morocco", flag: "🇲🇦" },
  { name: "Mozambique", flag: "🇲🇿" },
  { name: "Myanmar", flag: "🇲🇲" },
  { name: "Namibia", flag: "🇳🇦" },
  { name: "Nauru", flag: "🇳🇷" },
  { name: "Nepal", flag: "🇳🇵" },
  { name: "Netherlands", flag: "🇳🇱" },
  { name: "New Zealand", flag: "🇳🇿" },
  { name: "Nicaragua", flag: "🇳🇮" },
  { name: "Niger", flag: "🇳🇪" },
  { name: "Nigeria", flag: "🇳🇬" },
  { name: "North Korea", flag: "🇰🇵" },
  { name: "North Macedonia", flag: "🇲🇰" },
  { name: "Norway", flag: "🇳🇴" },
  { name: "Oman", flag: "🇴🇲" },
  { name: "Pakistan", flag: "🇵🇰" },
  { name: "Palau", flag: "🇵🇼" },
  { name: "Panama", flag: "🇵🇦" },
  { name: "Papua New Guinea", flag: "🇵🇬" },
  { name: "Paraguay", flag: "🇵🇾" },
  { name: "Peru", flag: "🇵🇪" },
  { name: "Philippines", flag: "🇵🇭" },
  { name: "Poland", flag: "🇵🇱" },
  { name: "Portugal", flag: "🇵🇹" },
  { name: "Qatar", flag: "🇶🇦" },
  { name: "Romania", flag: "🇷🇴" },
  { name: "Russia", flag: "🇷🇺" },
  { name: "Rwanda", flag: "🇷🇼" },
  { name: "Saint Kitts and Nevis", flag: "🇰🇳" },
  { name: "Saint Lucia", flag: "🇱🇨" },
  { name: "Saint Vincent and the Grenadines", flag: "🇻🇨" },
  { name: "Samoa", flag: "🇼🇸" },
  { name: "San Marino", flag: "🇸🇲" },
  { name: "Sao Tome and Principe", flag: "🇸🇹" },
  { name: "Saudi Arabia", flag: "🇸🇦" },
  { name: "Senegal", flag: "🇸🇳" },
  { name: "Serbia", flag: "🇷🇸" },
  { name: "Seychelles", flag: "🇸🇨" },
  { name: "Sierra Leone", flag: "🇸🇱" },
  { name: "Singapore", flag: "🇸🇬" },
  { name: "Slovakia", flag: "🇸🇰" },
  { name: "Slovenia", flag: "🇸🇮" },
  { name: "Solomon Islands", flag: "🇸🇧" },
  { name: "Somalia", flag: "🇸🇴" },
  { name: "South Africa", flag: "🇿🇦" },
  { name: "South Korea", flag: "🇰🇷" },
  { name: "South Sudan", flag: "🇸🇸" },
  { name: "Spain", flag: "🇪🇸" },
  { name: "Sri Lanka", flag: "🇱🇰" },
  { name: "Sudan", flag: "🇸🇩" },
  { name: "Suriname", flag: "🇸🇷" },
  { name: "Sweden", flag: "🇸🇪" },
  { name: "Switzerland", flag: "🇨🇭" },
  { name: "Syria", flag: "🇸🇾" },
  { name: "Taiwan", flag: "🇹🇼" },
  { name: "Tajikistan", flag: "🇹🇯" },
  { name: "Tanzania", flag: "🇹🇿" },
  { name: "Thailand", flag: "🇹🇭" },
  { name: "Timor-Leste", flag: "🇹🇱" },
  { name: "Togo", flag: "🇹🇬" },
  { name: "Tonga", flag: "🇹🇴" },
  { name: "Trinidad and Tobago", flag: "🇹🇹" },
  { name: "Tunisia", flag: "🇹🇳" },
  { name: "Turkey", flag: "🇹🇷" },
  { name: "Turkmenistan", flag: "🇹🇲" },
  { name: "Tuvalu", flag: "🇹🇻" },
  { name: "Uganda", flag: "🇺🇬" },
  { name: "Ukraine", flag: "🇺🇦" },
  { name: "United Arab Emirates", flag: "🇦🇪" },
  { name: "United Kingdom", flag: "🇬🇧" },
  { name: "United States", flag: "🇺🇸" },
  { name: "Uruguay", flag: "🇺🇾" },
  { name: "Uzbekistan", flag: "🇺🇿" },
  { name: "Vanuatu", flag: "🇻🇺" },
  { name: "Vatican City", flag: "🇻🇦" },
  { name: "Venezuela", flag: "🇻🇪" },
  { name: "Vietnam", flag: "🇻🇳" },
  { name: "Yemen", flag: "🇾🇪" },
  { name: "Zambia", flag: "🇿🇲" },
  { name: "Zimbabwe", flag: "🇿🇼" }
];

  countryInput.addEventListener("input", function () {
    const input = this.value.toLowerCase();
    countryList.innerHTML = "";

    if (input.length === 0) {
      countryList.style.display = "none";
      return;
    }

    const filtered = countries.filter(c => c.name.toLowerCase().startsWith(input));

    filtered.forEach(c => {
      const li = document.createElement("li");
      li.textContent = `${c.flag} ${c.name}`;
      li.addEventListener("click", () => {
        countryInput.value = c.name;
        countryList.innerHTML = "";
        countryList.style.display = "none";
      });
      countryList.appendChild(li);
    });

    countryList.style.display = filtered.length ? "block" : "none";
  });
  
  document.addEventListener("click", function (e) {
    if (!countryList.contains(e.target) && e.target !== countryInput) {
      countryList.style.display = "none";
    }
  });
</script>

<footer>
  <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
</footer>

</body>
</html>
