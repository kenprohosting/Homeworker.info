<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location:employee_login.php");
    exit();
}

require_once("db_connect.php");

$employee_id = $_SESSION['employee_id'];

// Fetch employee profile picture
$profile_sql = "SELECT Profile_pic FROM employees WHERE ID = ?";
$profile_stmt = $conn->prepare($profile_sql);
$profile_stmt->execute([$employee_id]);
$profile_data = $profile_stmt->fetch(PDO::FETCH_ASSOC);
// Store the profile pic path if it exists
$has_profile_pic = (!empty($profile_data['Profile_pic']) && file_exists($profile_data['Profile_pic']));
$profile_pic_path = $has_profile_pic ? $profile_data['Profile_pic'] : '';

// Confirm or Cancel Booking
if (isset($_GET['action'], $_GET['bid']) && in_array($_GET['action'], ['confirm', 'cancel'])) {
    $newStatus = $_GET['action'] === 'confirm' ? 'confirmed' : 'cancelled';
    $stmt = $conn->prepare("UPDATE bookings SET Status = ? WHERE ID = ? AND Employee_ID = ?");
    $stmt->execute([$newStatus, $_GET['bid'], $employee_id]);
    header("Location:employee_dashboard.php");
    exit();
}

// Fetch Bookings for this Employee
$stmt = $conn->prepare("
    SELECT b.*, e.Name AS employer_name 
    FROM bookings b 
    JOIN employer e ON b.Homeowner_ID = e.ID 
    WHERE b.Employee_ID = ?
    ORDER BY b.Booking_date DESC
");
$stmt->execute([$employee_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="icon" type="image/png" href="favicon.png">
    <title>Employee Dashboard - Homeworker Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            font-family: 'Segoe UI', sans-serif !important;
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
        
        /* User greeting */
        .user-greeting {
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            font-family: 'Segoe UI', sans-serif;
        }
        
        /* Profile image styling */
        .profile-img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            margin-left: 15px;
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
        
        /* Bookings table styling */
        .bookings-table {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
            margin-top: 20px;
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
        
        /* Action buttons styling */
        .btn {
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            font-size: 12px;
            margin: 2px;
            display: inline-block;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn.confirm {
            background: linear-gradient(90deg, #4caf50 0%, #66bb6a 100%);
            color: white;
        }
        
        .btn.confirm:hover {
            background: linear-gradient(90deg, #388e3c 0%, #4caf50 100%);
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(76,175,80,0.3);
        }
        
        .btn.cancel {
            background: linear-gradient(90deg, #d32f2f 0%, #f44336 100%);
            color: white;
        }
        
        .btn.cancel:hover {
            background: linear-gradient(90deg, #b71c1c 0%, #d32f2f 100%);
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(211,47,47,0.3);
        }
        
        .btn.disabled {
            background: #6c757d;
            color: white;
            pointer-events: none;
            opacity: 0.7;
        }
        
        /* Status styling */
        .status-pending {
            color: #ff9800;
            font-weight: bold;
        }
        
        .status-confirmed {
            color: #4caf50;
            font-weight: bold;
        }
        
        .status-cancelled {
            color: #f44336;
            font-weight: bold;
        }
        
        .status-completed {
            color: #2196f3;
            font-weight: bold;
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
            
            .profile-img {
                margin-left: 0;
                margin-top: 10px;
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
            <li><span class="user-greeting">Hello, <?= htmlspecialchars($_SESSION['employee_name']) ?></span></li>
            <li><a class="nav-btn" href="browse_jobs.php">Browse Jobs</a></li>
            <li><a class="nav-btn" href="my_applications.php">My Applications</a></li>
            <li><a class="nav-btn" href="employee_profile.php">Update Profile</a></li>
            <li><a class="nav-btn" href="employee_logout.php">Logout</a></li>
            <li>
                <?php if ($has_profile_pic): ?>
                    <img src="<?= htmlspecialchars($profile_pic_path) ?>" class="profile-img" alt="Profile Pic">
                <?php else: ?>
                    <svg width="40" height="40" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" class="profile-img" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                        <rect width="120" height="120" fill="#197b88" rx="60"/>
                        <g fill="#ffffff" opacity="0.8">
                            <circle cx="60" cy="40" r="18"/>
                            <path d="M30 100 C30 85, 42 75, 60 75 C78 75, 90 85, 90 100 L30 100 Z"/>
                        </g>
                    </svg>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
</header>

<div class="form-container">
    <h2>My Bookings</h2>

    <?php if (empty($bookings)): ?>
        <div class="no-bookings">
            <h3>No bookings yet</h3>
            <p>You have no bookings at the moment. Start by browsing available jobs and applying.</p>
            <a href="browse_jobs.php" class="nav-btn">Browse Jobs</a>
        </div>
    <?php else: ?>
        <div class="bookings-table">
            <table>
                <thead>
                    <tr>
                        <th>Employer</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['employer_name']) ?></td>
                        <td><?= htmlspecialchars($b['Service_type']) ?></td>
                        <td><?= htmlspecialchars($b['Booking_date']) ?></td>
                        <td><?= htmlspecialchars($b['Start_time']) ?> - <?= htmlspecialchars($b['End_time']) ?></td>
                        <td>
                            <span class="status-<?= strtolower($b['Status']) ?>">
                                <?= htmlspecialchars($b['Status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($b['Status'] === 'pending'): ?>
                                <a href="?action=confirm&bid=<?= $b['ID'] ?>" 
                                   class="btn confirm"
                                   onclick="return confirm('Are you sure you want to confirm this job booking?')"><!-- Change naming-con : jean luc 26 SEP 25 -->
                                    Confirm Job
                                </a>
                                <a href="?action=cancel&bid=<?= $b['ID'] ?>" 
                                   class="btn cancel"
                                   onclick="return confirm('Are you sure you want to decline this job booking?')"><!-- Change naming-con : jean luc 26 SEP 25 -->
                                    Decline Job
                                </a>
                            <?php else: ?>
                                <span class="btn disabled">No Action</span>
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

</body>
</html>