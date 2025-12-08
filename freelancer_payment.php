<?php
session_start();
require_once('@pdo.php');

// Connect to the database using PDO helper
PDO_Connect('mysql:host=localhost;dbname=esoma_homeworker;charset=utf8mb4', 'esoma_homeworker', 'Kenyan@254'); // LIVE DB credentials

if (!isset($_SESSION['freelancer_id'])) {
    header("Location:freelancer_login.php");
    exit();
}

$employer_id = $_SESSION['freelancer_id'];
$booking_id = isset($_GET['bid']) ? intval($_GET['bid']) : 0;
$errors = [];
$success = '';

// Fetch booking, employer, and employee details
if ($booking_id) {
    $booking = PDO_FetchRow(
        "SELECT b.*, fr.Name AS freelancer_name, fr.phone AS freelancer_phone, fr.country AS freelancer_country, fr.county_province AS freelancer_county_province, fr.email AS freelancer_email, fr.Skills AS freelancer_skills, fr.Education_level AS freelancer_education, fr.Gender AS freelancer_gender, fr.Age AS freelancer_age, fr.Social_referee AS freelancer_referee FROM bookings b JOIN freelancers fr ON b.freelancer_ID = fr.ID WHERE b.ID = ? AND b.Homeowner_ID = ?",
        [$booking_id, $freelancer_id]
    );
    if (!$booking) {
        $errors[] = "Invalid booking.";
    }
    // Fetch employer details
    $freelancer = PDO_FetchRow("SELECT Name, email FROM employer WHERE ID = ?", [$freelancer_id]);
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
            <li><a href="freelancer_dashboard.php">← Back</a></li>
            <li><a href="freelancer_logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<div class="form-container">
    <h2>Pay $1 to Access Employee Contact Details</h2>
    <?php
    if ($errors) foreach ($errors as $e) echo "<p class='error'>$e</p>";
    if ($success) echo "<p class='success'>$success</p>";
    ?>

    <?php if (isset($booking) && $booking && isset($freelancer) && $freelancer): ?>
        <button
            id="intasend-button"
            class="intaSendPayButton"
            data-amount="1"
            data-currency="USD"
            data-email="<?= htmlspecialchars($employer['email']) ?>"
            data-description="Contact details access for booking #<?= $booking_id ?>"
            data-redirect_url="https://homeworker.info/payment_callback.php?bid=<?= $booking_id ?>">
            Pay $1 with IntaSend
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
