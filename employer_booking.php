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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("INSERT INTO bookings 
        (Homeowner_ID, Employee_ID, Service_type, Booking_date, Status)
        VALUES (?, ?, ?, ?, 'pending')");

    $stmt->execute([
        $_SESSION['employer_id'],
        $employee_id,
        $employee['Skills'],
        $book_date
    ]);

    echo "<p style='color:green;'>✅ Booking submitted successfully!</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
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
    </style>
</head>
<body>

<div class="box">
    <h2>Book <?= htmlspecialchars($employee['Name']) ?></h2>
    
    <form method="POST">
        <label>Book Date:</label>
        <input type="text" name="book_date" value="<?= $book_date ?>" readonly>

        <label>Skill Booked:</label>
        <input type="text" name="skill" value="<?= htmlspecialchars($employee['Skills']) ?>" readonly>

        <button type="submit">Submit Booking</button>
    </form>
</div>

</body>
</html>
