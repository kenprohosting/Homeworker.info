<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location:employee_access.php");
    exit();
}

require_once("db_connect.php");

$employee_id = $_SESSION['employee_id'];

// Fetch employee profile picture
$profile_sql = "SELECT Profile_pic FROM employees WHERE ID = ?";
$profile_stmt = $conn->prepare($profile_sql);
$profile_stmt->execute([$employee_id]);
$profile_data = $profile_stmt->fetch(PDO::FETCH_ASSOC);
$profile_pic_path = (!empty($profile_data['Profile_pic']) && file_exists($profile_data['Profile_pic']))
    ? $profile_data['Profile_pic']
    : 'uploads/default.jpg'; // fallback image

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
    <title>Employee Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }

        header {
            background: rgb(24, 123, 136);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        h1 {
            margin: 0;
        }

        .top-links {
            display: flex;
            align-items: center;
        }

        .top-links a {
            color: white;
            margin-left: 20px;
            text-decoration: none;
            font-weight: bold;
        }

        .profile-img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            margin-left: 20px;
        }

        .container {
            padding: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: center;
        }

        th {
            background: #e0e0e0;
        }

        .btn {
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .btn.confirm {
            background: #4caf50;
            color: white;
        }

        .btn.cancel {
            background: #e53935;
            color: white;
        }

        .btn.disabled {
            background: gray;
            color: white;
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .top-links {
                flex-direction: column;
                align-items: flex-end;
            }

            .top-links a {
                margin: 5px 0;
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
    <h1>Welcome, <?= htmlspecialchars($_SESSION['employee_name']) ?></h1>
    <div class="top-links">
        <button class="nav-btn" onclick="window.history.back()">← Back</button>
        <a href="browse_jobs.php">Browse Jobs</a>
        <a href="my_applications.php">My Applications</a>
        <a href="employee_profile.php">Update Profile</a>
        <a href="employee_logout.php">Logout</a>
        <img src="<?= htmlspecialchars($profile_pic_path) ?>" class="profile-img" alt="Profile Pic">
    </div>
</header>

<div class="container">
    <h2>My Bookings</h2>

    <?php if (empty($bookings)): ?>
        <p>You have no bookings yet.</p>
    <?php else: ?>
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
                    <td><?= htmlspecialchars($b['Status']) ?></td>
                    <td>
                        <?php if ($b['Status'] === 'pending'): ?>
                            <a href="?action=confirm&bid=<?= $b['ID'] ?>" class="btn confirm">Confirm</a>
                            <a href="?action=cancel&bid=<?= $b['ID'] ?>" class="btn cancel">Cancel</a>
                        <?php else: ?>
                            <span class="btn disabled">No Action</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
