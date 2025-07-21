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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($booking) && $booking) {
    $phone = trim($_POST['phone']);
    // Convert phone to international format if needed
    if (preg_match('/^07\\d{8}$/', $phone)) {
        $phone = '254' . substr($phone, 1);
    }
    $amount = 5; // KES 5 for contact details
    $description = "Contact details access for booking #$booking_id";
    $email = $employer['email'];
    $name = $employer['Name'];

    // IntaSend API keys and endpoint
    $public_key = "ISPubKey_live_40f25458-716c-47c5-b049-786fd1f3a1ce";
    $endpoint = "https://payment.intasend.com/api/v1/checkout/";

    // Prepare payment data
    $data = [
        "currency" => "KES",
        "amount" => $amount,
        "email" => $email,
        "description" => $description,
        "redirect_url" => "https://homeworker.info/payment_callback.php?bid=$booking_id", // Corrected path
        "phone_number" => $phone
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $public_key",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['url'])) {
        // Redirect user to IntaSend payment page
        header("Location: " . $result['url']);
        exit();
    } else {
        $errors[] = "Payment initiation failed. " . (isset($result['message']) ? $result['message'] : json_encode($result));
    }
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
    <h2>Pay KES 5 to Access Employee Contact Details</h2>

    <?php
    if ($errors) foreach ($errors as $e) echo "<p class='error'>$e</p>";
    if ($success) echo "<p class='success'>$success</p>";
    ?>

    <?php if (isset($booking) && $booking): ?>
        <form method="POST">
            <label for="phone">Phone Number (for payment):</label>
            <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($booking['employee_phone']) ?>" required>
            <button type="submit" class="btn">Pay KES 5 with IntaSend</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
