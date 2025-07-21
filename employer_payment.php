<?php
session_start();
require_once('@pdo.php');

// Connect to the database using PDO helper
PDO_Connect('mysql:host=localhost;dbname=esoma_homeworker;charset=utf8mb4', 'esoma_homeworker', 'Kenyan@254'); // LIVE DB credentials

if (!isset($_SESSION['employer_id'])) {
    header("Location:employer_login.php");
    exit();
}

$employer_id = $_SESSION['employer_id'];
$booking_id = isset($_GET['bid']) ? intval($_GET['bid']) : 0;
$errors = [];
$success = '';

// Fetch booking, employer, and employee details
if ($booking_id) {
    $booking = PDO_FetchRow(
        "SELECT b.*, emp.Name AS employee_name, emp.phone AS employee_phone, emp.country AS employee_country, emp.county_province AS employee_county_province, emp.email AS employee_email, emp.Skills AS employee_skills, emp.Education_level AS employee_education, emp.Gender AS employee_gender, emp.Age AS employee_age, emp.Social_referee AS employee_referee FROM bookings b JOIN employees emp ON b.Employee_ID = emp.ID WHERE b.ID = ? AND b.Homeowner_ID = ?",
        [$booking_id, $employer_id]
    );
    if (!$booking) {
        $errors[] = "Invalid booking.";
    }
    // Fetch employer details
    $employer = PDO_FetchRow("SELECT Name, email FROM employer WHERE ID = ?", [$employer_id]);
} else {
    $errors[] = "No booking selected.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Make Payment for Contact Details</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <div class="logo"></div>
    <nav>
        <ul class="nav-links">
            <li><a href="employer_dashboard.php">← Back</a></li>
            <li><a href="employer_logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<div class="form-container">
    <h2>Pay KES 10 to Access Employee Contact Details</h2>
    <?php
    if ($errors) foreach ($errors as $e) echo "<p class='error'>$e</p>";
    if ($success) echo "<p class='success'>$success</p>";
    ?>

    <?php if (isset($booking) && $booking && isset($employer) && $employer): ?>
        <button
            id="intasend-button"
            class="intaSendPayButton"
            data-amount="10"
            data-currency="KES"
            data-email="<?= htmlspecialchars($employer['email']) ?>"
            data-description="Contact details access for booking #<?= $booking_id ?>"
            data-redirect_url="https://homeworker.info/payment_callback.php?bid=<?= $booking_id ?>">
            Pay KES 10 with IntaSend
        </button>
        <script src="https://unpkg.com/intasend-inlinejs-sdk@4.0.1/build/intasend-inline.js"></script>
        <script>
            new window.IntaSend({
                publicAPIKey: "ISPubKey_live_40f25458-716c-47c5-b049-786fd1f3a1ce", // Your live public key
                live: true // set to true for live environment
            })
            .on("COMPLETE", (results) => {
                // Redirect to callback page with IntaSend's checkout_request_id
                window.location.href = "payment_callback.php?bid=<?= $booking_id ?>&checkout_request_id=" + results.checkout_request_id;
            })
            .on("FAILED", (results) => { alert("Payment failed. Please try again."); })
            .on("IN-PROGRESS", (results) => { /* Optionally show progress */ });
        </script>
    <?php endif; ?>
</div>

</body>
</html>
