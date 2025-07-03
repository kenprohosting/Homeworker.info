<?php
session_start();
require_once('db_connect.php');

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
    $stmt = $conn->prepare("SELECT b.*, emp.Name AS employee_name, emp.Contact AS employee_contact, emp.Location AS employee_location, emp.email AS employee_email, emp.Skills AS employee_skills, emp.Education_level AS employee_education, emp.Gender AS employee_gender, emp.Age AS employee_age, emp.Social_referee AS employee_referee FROM bookings b JOIN employee emp ON b.Employee_ID = emp.ID WHERE b.ID = ? AND b.Homeowner_ID = ?");
    $stmt->execute([$booking_id, $employer_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$booking) {
        $errors[] = "Invalid booking.";
    }
    // Fetch employer details
    $empstmt = $conn->prepare("SELECT Name, email FROM employer WHERE ID = ?");
    $empstmt->execute([$employer_id]);  $employer = $empstmt->fetch(PDO::FETCH_ASSOC);
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

    // IntaSend API keys
    $api_key = "ISSecretKey_test_f34b63be-83ef-4a36-a377-6607136d1ee0";
    $publishable_key = "ISPubKey_test_b46261c4-f53f-4986-9b98-566767bc6434";

    // Prepare payment data
    $data = [
        "currency" => "KES",
        "amount" => $amount,
        "email" => $email,
        "description" => $description,
        "redirect_url" => "http://localhost/houselp/payment_callback.php?bid=$booking_id", // Change to your domain in production
        "phone_number" => $phone
    ];

    $ch = curl_init('https://api.intasend.com/api/v1/checkout/');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $api_key",
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
    <div class="logo">Houselp Connect</div>
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

    <?php if (isset($booking) && $booking): ?>
        <!-- IntaSend Payment Button -->
        <button 
            id="intasend-button" 
            class="intaSendPayButton"  
            data-amount="10" 
            data-currency="KES"
            data-email="<?= htmlspecialchars($employer['email']) ?>"
            data-description="Contact details access for booking #<?= $booking_id ?>"
            data-redirect_url="http://localhost/houselp/payment_callback.php?bid=<?= $booking_id ?>">
            Pay KES 10 with IntaSend
        </button>
        <script src="https://unpkg.com/intasend-inlinejs-sdk@4.0.1/build/intasend-inline.js"></script>
        <script>
            new window.IntaSend({
                publicAPIKey: "ISPubKey_test_e767a17c-5afd-4a1b-8754-9415379df6b6", // Use your test/live key
                live: false // set to true when going live
            })
            .on("COMPLETE", (results) => {
                // Redirect to callback page
                window.location.href = "payment_callback.php?bid=<?= $booking_id ?>";
            })
            .on("FAILED", (results) => { alert("Payment failed. Please try again."); })
            .on("IN-PROGRESS", (results) => { /* Optionally show progress */ });
        </script>
    <?php endif; ?>
</div>

</body>
</html>
