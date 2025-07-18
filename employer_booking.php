<?php
session_start();
if (!isset($_SESSION['employer_id'])) {
    header("Location: employer_login.php");
    exit();
}
require_once("db_connect.php");

$employee_id = $_GET['eid'] ?? null;

if (!$employee_id) {
    echo "Invalid employee ID.";
    exit();
}

// Fetch employee details
$stmt = $conn->prepare("SELECT Name, Skills FROM employees WHERE ID = ?");
$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    echo "Employee not found.";
    exit();
}

$book_date = date('Y-m-d'); // today's date

// Check if there's an existing booking
$stmt = $conn->prepare("SELECT ID FROM bookings WHERE Homeowner_ID = ? AND Employee_ID = ? AND Status = 'pending'");
$stmt->execute([$_SESSION['employer_id'], $employee_id]);
$existing_booking = $stmt->fetch(PDO::FETCH_ASSOC);

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'unbook') {
        // Handle unbook - update status to cancelled instead of deleting
        $stmt = $conn->prepare("UPDATE bookings SET Status = 'cancelled' WHERE Homeowner_ID = ? AND Employee_ID = ? AND Status = 'pending'");
        $stmt->execute([$_SESSION['employer_id'], $employee_id]);
        $message = "<p style='color:orange;'>🔄 Booking cancelled successfully! The cancelled booking has been added to your bookings history.</p>";
        $existing_booking = null; // Reset the booking status
    } else {
        // Handle book
        if (!$existing_booking) {
            $stmt = $conn->prepare("INSERT INTO bookings 
                (Homeowner_ID, Employee_ID, Service_type, Booking_date, Status)
                VALUES (?, ?, ?, ?, 'pending')");

            $stmt->execute([
                $_SESSION['employer_id'],
                $employee_id,
                $employee['Skills'],
                $book_date
            ]);
            $message = "<p style='color:green;'>✅ Booking submitted successfully!</p>";
            
            // Refresh booking status
            $stmt = $conn->prepare("SELECT ID FROM bookings WHERE Homeowner_ID = ? AND Employee_ID = ? AND Status = 'pending'");
            $stmt->execute([$_SESSION['employer_id'], $employee_id]);
            $existing_booking = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $message = "<p style='color:blue;'>ℹ️ You already have a pending booking with this employee.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="favicon.png">
    <title>Book <?= htmlspecialchars($employee['Name']) ?></title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 30px; }
        .box {
            background: white;
            max-width: 500px;
            margin: auto;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input, button {
            width: 100%; padding: 10px;
            margin-top: 5px; border-radius: 4px;
        }
        button {
            background-color: #00695c;
            color: white;
            border: none;
            margin-top: 20px;
        }
        .back-btn {
            display: inline-block;
            padding: 8px 15px;
            background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%);
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            background: linear-gradient(90deg, #17606e 0%, #1ec8c8 100%);
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(24,123,136,0.3);
        }
        .unbook-btn {
            background-color: #d32f2f;
            color: white;
            border: none;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        .unbook-btn:hover {
            background-color: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(211,47,47,0.3);
        }
        .button-container {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .button-container button {
            flex: 1;
            margin-top: 0;
        }
    </style>
</head>
<body>

<div class="box">
    <a href="<?= isset($_GET['from']) && $_GET['from'] === 'bookings' ? 'employer_bookings.php' : 'employer_dashboard.php' ?>" class="back-btn">
        ← Back to <?= isset($_GET['from']) && $_GET['from'] === 'bookings' ? 'Bookings' : 'Dashboard' ?>
    </a>
    <h2>Book <?= htmlspecialchars($employee['Name']) ?></h2>
    
    <?= $message ?>
    
    <form method="POST">
        <label>Book Date:</label>
        <input type="text" name="book_date" value="<?= $book_date ?>" readonly>

        <label>Skill Booked:</label>
        <input type="text" name="skill" value="<?= htmlspecialchars($employee['Skills']) ?>" readonly>

        <?php if ($existing_booking): ?>
            <div class="button-container">
                <button type="submit" name="action" value="unbook" class="unbook-btn">Cancel Booking</button>
            </div>
            <p style="color: #00695c; font-weight: bold;">✅ You have a pending booking with this employee</p>
        <?php else: ?>
            <button type="submit">Submit Booking</button>
        <?php endif; ?>
    </form>
</div>

</body>
</html>
