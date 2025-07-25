<?php
session_start();
require_once('db_connect.php');

if (!isset($_SESSION['employer_reg_data'])) {
    header("Location: employer_register.php");
    exit();
}

$reg_data = $_SESSION['employer_reg_data'];
$errors = [];
$success = '';
$payment_success = false;

// IntaSend API key
$publishable_key = "ISPubKey_live_40f25458-716c-47c5-b049-786fd1f3a1ce";

$checkout_request_id = isset($_GET['checkout_request_id']) ? $_GET['checkout_request_id'] : null;

if ($checkout_request_id) {
    $ch = curl_init("https://api.intasend.com/api/v1/checkout/" . urlencode($checkout_request_id) . "/");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $publishable_key",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);
    if (isset($result['status']) && strtolower($result['status']) === 'complete') {
        $payment_success = true;
    } else {
        $errors[] = "Payment failed or not completed. Please try again.";
    }
} else {
    $errors[] = "No payment reference found. Please try again.";
}

if ($payment_success) {
    $stmt = $conn->prepare("INSERT INTO employer (Name, Country, Location, Residence_type, Contact, Gender, Email, Password_hash, Address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([
        $reg_data['name'],
        $reg_data['country'],
        $reg_data['location'],
        $reg_data['residence'],
        $reg_data['contact'],
        $reg_data['gender'],
        $reg_data['email'],
        $reg_data['password_hash'],
        $reg_data['address']
    ]);

    if ($result) {
        unset($_SESSION['employer_reg_data']);
        $success = "Registration successful. Please <a href='employer_login.php'>login</a>.";
    } else {
        $errors[] = "Registration failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration Callback</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <div class="logo"></div>
    <nav>
        <ul class="nav-links">
            <li><a href="employer_register.php">← Back</a></li>
        </ul>
    </nav>
</header>

<div class="form-container">
    <h2>Registration Status</h2>
    <?php
    if ($errors) foreach ($errors as $e) echo "<p class='error'>$e</p>";
    if ($success) echo "<p class='success'>$success</p>";
    ?>
    <?php if (!$payment_success): ?>
        <a href="employer_register_payment.php" class="btn">Retry Payment</a>
    <?php endif; ?>
</div>

</body>
</html>