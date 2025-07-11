<?php
session_start();
if (!isset($_SESSION['employer_id'])) {
    header("Location:employer_login.php");
    exit();
}
require_once('db_connect.php');

// Fetch employees with filters
$filter_sql = "SELECT * FROM employee WHERE 1";
$params = [];

if (!empty($_GET['skill'])) {
    $filter_sql .= " AND Skills LIKE ?";
    $params[] = '%' . $_GET['skill'] . '%';
}
if (!empty($_GET['country'])) {
    $filter_sql .= " AND Country LIKE ?";
    $params[] = '%' . $_GET['country'] . '%';
}
if (!empty($_GET['location'])) {
    $filter_sql .= " AND Location LIKE ?";
    $params[] = '%' . $_GET['location'] . '%';
}
if (!empty($_GET['gender'])) {
    $filter_sql .= " AND Gender = ?";
    $params[] = $_GET['gender'];
}
if (!empty($_GET['residence'])) {
    $filter_sql .= " AND residence_type = ?";
    $params[] = $_GET['residence'];
}

$stmt = $conn->prepare($filter_sql);
$stmt->execute($params);
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch employer's bookings
$stmt2 = $conn->prepare("
    SELECT b.*, emp.Name AS employee_name 
    FROM bookings b 
    JOIN employee emp ON b.Employee_ID = emp.ID 
    WHERE b.Homeowner_ID = ?
    ORDER BY b.Booking_date DESC
");
$stmt2->execute([$_SESSION['employer_id']]);
$bookings = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Employer Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; margin: 0; }
    header {
      background: rgb(24, 123, 136);
      color: white;
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      position: relative;
    }
    .logo { font-size: 1.5em; font-weight: bold; }
    nav ul { list-style: none; display: flex; gap: 20px; margin: 0; padding: 0; }
    nav a { color: white; text-decoration: none; }
    .form-container { padding: 30px; }
    .filter-form input, .filter-form select, .filter-form button {
      padding: 10px; margin-right: 10px; margin-top: 10px;
    }
    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 20px;
      margin-top: 30px;
    }
    .card {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      text-align: center;
    }
    .card img {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 10px;
    }
    .btn {
      display: inline-block;
      padding: 10px 16px;
      background: #00695c;
      color: white;
      border: none;
      border-radius: 5px;
      margin-top: 10px;
      text-decoration: none;
    }
    table {
      width: 100%;
      margin-top: 20px;
      border-collapse: collapse;
      background: white;
      display: none;
    }
    table.show { display: table; }
    th, td {
      padding: 12px;
      border: 1px solid #ccc;
      text-align: center;
    }
    th { background: #eeeeee; }

    #countryList {
      list-style: none;
      background: white;
      position: absolute;
      z-index: 999;
      border: 1px solid #ccc;
      max-height: 150px;
      overflow-y: auto;
      display: none;
      width: 200px;
      padding: 0;
      margin: 0;
    }
    #countryList li {
      padding: 8px 10px;
      cursor: pointer;
    }
    #countryList li:hover {
      background: #f0f0f0;
    }

    @media (max-width: 768px) {
      header {
        flex-direction: column;
        align-items: flex-start;
      }
      nav ul {
        flex-direction: column;
        width: 100%;
      }
      .card-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

<header>
  <div class="logo">Homewoker Connect</div>
  <nav>
    <ul class="nav-links">
      <li>Hello, <?= htmlspecialchars($_SESSION['employer_name']) ?></li>
      <li><a href="post_job.php">Post Job</a></li>
      <li><a href="manage_jobs.php">My Jobs</a></li>
      <li><a href="#" id="toggleBookings">My Bookings</a></li>
      <li><a href="employer_logout.php">Logout</a></li>
    </ul>
  </nav>
</header>

<div class="form-container">
  <h2>Find a Homewoker</h2>

  <form method="GET" class="filter-form">
    <input type="text" name="skill" placeholder="Skill (e.g. Driving)">
    <input type="text" id="countryInput" name="country" placeholder="Country" autocomplete="off" required>
    <ul id="countryList" class="country-dropdown"></ul>
    <input type="text" name="location" placeholder="County or Province">
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
    <button type="submit" class="btn">Search</button>
  </form>

  <!-- My Bookings Table (hidden by default) -->
  <div id="bookingsTableContainer">
    <h2>My Bookings</h2>
    <?php if (count($bookings) == 0): ?>
      <p>You have no bookings yet.</p>
    <?php else: ?>
      <table id="bookingsTable">
        <thead>
          <tr>
            <th>Employee</th>
            <th>Service</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Payment</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bookings as $b): ?>
          <tr>
            <td><?= htmlspecialchars($b['employee_name']) ?></td>
            <td><?= htmlspecialchars($b['Service_type']) ?></td>
            <td><?= htmlspecialchars($b['Booking_date']) ?></td>
            <td><?= $b['Start_time'] ?> - <?= $b['End_time'] ?></td>
            <td><?= htmlspecialchars($b['Status']) ?></td>
            <td>
              <?php if ($b['Status'] === 'confirmed'): ?>
                  <a href="employer_payment.php?bid=<?= $b['ID'] ?>" class="btn">Make Payment</a>
              <?php elseif ($b['Status'] === 'completed'): ?>
                  <span style="color:green;">Paid</span>
              <?php elseif ($b['Status'] === 'cancelled'): ?>
                  <span style="color:red;">Cancelled</span>
              <?php else: ?>
                  <span style="color:gray;">Pending</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <!-- Employee Cards -->
  <div class="card-grid">
    <?php foreach ($employees as $emp): ?>
      <div class="card">
        <?php
          $profile = 'uploads/default.jpg';
          if (!empty($emp['profile_pic']) && file_exists(__DIR__ . '/' . $emp['profile_pic'])) {
              $profile = $emp['profile_pic'];
          }
        ?>
        <img src="<?= htmlspecialchars($profile) ?>" alt="Profile Picture">
        <h3><?= htmlspecialchars($emp['Name']) ?> (<?= $emp['Age'] ?>)</h3>
        <p><strong>Skill:</strong> <?= htmlspecialchars($emp['Skills']) ?></p>
        <p><strong>Country:</strong> <?= htmlspecialchars($emp['Country']) ?></p>
        <p><strong>Location:</strong> <?=htmlspecialchars($emp['Location']) ?></p>
        <p><strong>Language:</strong> <?= htmlspecialchars($emp['Language']) ?></p>
        <p><strong>Education:</strong> <?= htmlspecialchars($emp['Education_level']) ?></p>
        <a href="employer_booking.php?eid=<?= $emp['ID'] ?>" class="btn">Book Now</a>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- JS for Toggle + Country Flags -->
<script>
  const toggleBookings = document.getElementById("toggleBookings");
  const bookingsTable = document.getElementById("bookingsTable");
  const bookingsContainer = document.getElementById("bookingsTableContainer");

  toggleBookings.addEventListener("click", function(e) {
    e.preventDefault();
    bookingsTable.classList.toggle("show");
  });

  document.addEventListener("click", function(e) {
    if (!toggleBookings.contains(e.target) && !bookingsTable.contains(e.target)) {
      bookingsTable.classList.remove("show");
    }
  });

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
  { name: "Kenya", flag: "🇰🇪" },
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
</body>
</html>
