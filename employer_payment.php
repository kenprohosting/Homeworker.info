<?php
session_start();
require_once('db_connect.php');

if (!isset($_SESSION['employer_id'])) {
<<<<<<< HEAD
    header("Location:employer_login.php");
=======
    header("Location:login_employer.php");
>>>>>>> bf4d09db357fb0cddf6c0fc024c1eed1105fbecb
    exit();
}

$employer_id = $_SESSION['employer_id'];
<<<<<<< HEAD
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
=======
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['subscription_type'];
    $amount = $type === 'monthly' ? 500 : 5000;
    $method = $_POST['method'];
    $transaction_id = $_POST['transaction'];
    $description = ucfirst($type) . " subscription";

    // Save subscription payment
    $stmt = $conn->prepare("
        INSERT INTO payment (Booking_ID, Date, Amount, Description, Payment_method, Transaction_ID, Status, created_at)
        VALUES (NULL, CURDATE(), ?, ?, ?, ?, 'completed', NOW())
    ");
    $result = $stmt->execute([$amount, $description, $method, $transaction_id]);

    if ($result) {
        // Optionally, you can store subscription expiry separately
        $success = "Subscription successful! You can now book househelps.";
    } else {
        $errors[] = "Subscription failed. Please try again.";
>>>>>>> bf4d09db357fb0cddf6c0fc024c1eed1105fbecb
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<<<<<<< HEAD
    <title>Make Payment for Contact Details</title>
    <link rel="stylesheet" href="styles.css">
=======
    <title>Employer Subscription</title>
    <link rel="stylesheet" href="..css/styles.css">
>>>>>>> bf4d09db357fb0cddf6c0fc024c1eed1105fbecb
</head>
<body>

<header>
    <div class="logo">Houselp Connect</div>
    <nav>
        <ul class="nav-links">
<<<<<<< HEAD
            <li><a href="employer_dashboard.php">← Back</a></li>
            <li><a href="employer_logout.php">Logout</a></li>
=======
            <li><a href="dashboard.php">← Back</a></li>
            <li><a href="logout.php">Logout</a></li>
>>>>>>> bf4d09db357fb0cddf6c0fc024c1eed1105fbecb
        </ul>
    </nav>
</header>

<div class="form-container">
<<<<<<< HEAD
    <h2>Pay KES 10 to Access Employee Contact Details</h2>
=======
    <h2>Subscribe to Access the Platform</h2>
>>>>>>> bf4d09db357fb0cddf6c0fc024c1eed1105fbecb

    <?php
    if ($errors) foreach ($errors as $e) echo "<p class='error'>$e</p>";
    if ($success) echo "<p class='success'>$success</p>";
    ?>

<<<<<<< HEAD
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
=======
    <form method="POST">
        <label>Choose Subscription:</label>
        <select name="subscription_type" required>
            <option value="">-- Select --</option>
            <option value="monthly">Monthly - KES 500</option>
            <option value="yearly">Yearly - KES 5,000</option>
        </select>

        <label>Payment Method:</label>
        <select name="method" required>
            <option value="">-- Select --</option>
            <option value="mpesa">M-Pesa</option>
            <option value="cash">Cash</option>
        </select>

        <label>M-Pesa Transaction ID:</label>
        <input type="text" name="transaction" required>

        <button type="submit" class="btn">Submit Payment</button>
    </form>
>>>>>>> bf4d09db357fb0cddf6c0fc024c1eed1105fbecb
</div>

</body>
</html>
