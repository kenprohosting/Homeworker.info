<?php
session_start();
if (!isset($_SESSION['employer_id'])) {
    header("Location: employer_login.php");
    exit();
}

require_once('db_connect.php');

$message = '';

// Handle unbook action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'unbook') {
    $booking_id = $_POST['booking_id'];
    $stmt = $conn->prepare("UPDATE bookings SET Status = 'cancelled' WHERE ID = ? AND Homeowner_ID = ?");
    $stmt->execute([$booking_id, $_SESSION['employer_id']]);
    $message = "<div style='background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin-bottom: 20px;'>🔄 Booking cancelled successfully!</div>";
}

// Fetch employer's bookings with salary_expectation included : jean luc 26 SEP 25
$stmt = $conn->prepare("
    SELECT b.*, emp.Name AS employee_name, emp.salary_expectation 
    FROM bookings b 
    JOIN employees emp ON b.Employee_ID = emp.ID 
    WHERE b.Homeowner_ID = ?
    ORDER BY b.Booking_date DESC
");
$stmt->execute([$_SESSION['employer_id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <title>My Bookings - Homeworker Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        h1 {
            color: #197b88;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .bookings-table {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        
        th {
            background-color: #f3f3f3;
            font-weight: bold;
            color: #333;
        }
        
        .no-bookings {
            text-align: center;
            padding: 50px;
            color: #666;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .user-greeting {
            color: white;
            font-weight: 500;
            padding: 10px 16px;
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
        
        .unbook-btn {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        
        .unbook-btn:hover {
            background-color: #b71c1c;
            transform: translateY(-1px);
        }
        .hamburger {
          display: none;
          flex-direction: column;
          justify-content: center;
          align-items: center;
          width: 44px;
          height: 44px;
          background: none;
          border: none;
          cursor: pointer;
          margin-left: auto;
          z-index: 1002;
        }
        .hamburger .bar {
          width: 28px;
          height: 3px;
          background: #fff;
          margin: 4px 0;
          border-radius: 2px;
          transition: 0.3s;
          display: block;
        }
        @media (max-width: 900px) {
          .hamburger {
            display: flex;
          }
          .main-nav {
            width: 100%;
          }
          .nav-links {
            display: none !important;
            flex-direction: column;
            position: absolute;
            top: 60px;
            right: 0;
            left: 0;
            background: #0A4A70;
            z-index: 1001;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            padding: 18px 0 10px 0;
            border-radius: 0 0 12px 12px;
            gap: 0;
            margin: 0;
            width: 100vw;
            min-width: 0;
          }
          .nav-links.show {
            display: flex !important;
          }
          .nav-links li {
            width: 100%;
            text-align: center;
            margin: 0;
            padding: 0;
          }
          .nav-links li a, .nav-links li span {
            display: block;
            width: 100%;
            padding: 14px 0;
            margin: 0;
            border-radius: 0;
            border-bottom: 1px solid #197b88;
          }
          .nav-links li:last-child a {
            border-bottom: none;
          }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <img src="bghse.png" alt="Logo" style="height: 40px;">
    </div>
    <button class="hamburger" id="hamburgerBtn" aria-label="Open navigation" aria-expanded="false" aria-controls="mainNav" type="button">
      <span class="bar"></span>
      <span class="bar"></span>
      <span class="bar"></span>
    </button>
    <nav class="main-nav">
        <ul class="nav-links" id="mainNav">
            <li><span class="user-greeting">Hello, <?= htmlspecialchars($_SESSION['employer_name']) ?></span></li>
            <li><a class="nav-btn" href="employer_dashboard.php">Dashboard</a></li>
            <li><a class="nav-btn" href="post_job.php">Post Job</a></li>
            <li><a class="nav-btn" href="manage_jobs.php">My Jobs</a></li>
            <li><a class="nav-btn" href="employer_logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<div class="container">
    <h1>My Bookings</h1>
    
    <?= $message ?>
    
    <?php if (count($bookings) == 0): ?>
        <div class="no-bookings">
            <h3>No bookings yet</h3>
            <p>You have no bookings at the moment. Start by finding and booking homeworkers from your dashboard.</p>
            <a href="employer_dashboard.php" class="btn">Find Homeworkers</a>
        </div>
    <?php else: ?>
        <div class="bookings-table">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Service</th>
                        <th>Expected Salary</th> <!-- jean luc 26 SEP 25 -->
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['employee_name']) ?></td>
                        <td><?= htmlspecialchars($b['Service_type']) ?></td>
                        <td><?= htmlspecialchars($b['salary_expectation'] ?? 'N/A') ?></td> <!-- Expected Salary : jean luc 26 SEP 25 -->
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
                        <td>
                            <?php if ($b['Status'] === 'pending'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?= $b['ID'] ?>">
                                    <button type="submit" name="action" value="unbook" class="unbook-btn" 
                                            onclick="return confirm('Are you sure you want to cancel this booking?')">
                                        Cancel
                                    </button>
                                </form>
                            <?php elseif ($b['Status'] === 'cancelled'): ?>
                                <a href="employer_booking.php?eid=<?= $b['Employee_ID'] ?>" class="btn" style="font-size:12px; padding:6px 12px;">
                                    Rebook
                                </a>
                            <?php else: ?>
                                <span style="color:gray;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; <?= date("Y") ?> KenPro. All rights reserved.</p> | <a href="privacy_policy.php" style="text-decoration: none; color: inherit;">Privacy Policy</a>
</footer>

<script>
// Hamburger menu toggle
const hamburgerBtn = document.getElementById('hamburgerBtn');
const navLinks = document.getElementById('mainNav');
hamburgerBtn.addEventListener('click', function() {
  const expanded = hamburgerBtn.getAttribute('aria-expanded') === 'true';
  hamburgerBtn.setAttribute('aria-expanded', !expanded);
  navLinks.classList.toggle('show');
});
// Close menu when clicking outside (mobile only)
document.addEventListener('click', function(e) {
  if (window.innerWidth <= 900 && navLinks.classList.contains('show')) {
    if (!navLinks.contains(e.target) && e.target !== hamburgerBtn && !hamburgerBtn.contains(e.target)) {
      navLinks.classList.remove('show');
      hamburgerBtn.setAttribute('aria-expanded', 'false');
    }
  }
});
</script>

</body>
</html>