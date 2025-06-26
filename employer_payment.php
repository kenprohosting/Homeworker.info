<?php
session_start();
require_once('db_connect.php');

if (!isset($_SESSION['employer_id'])) {
    header("Location:login_employer.php");
    exit();
}

$employer_id = $_SESSION['employer_id'];
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
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employer Subscription</title>
    <link rel="stylesheet" href="..css/styles.css">
</head>
<body>

<header>
    <div class="logo">Houselp Connect</div>
    <nav>
        <ul class="nav-links">
            <li><a href="dashboard.php">← Back</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<div class="form-container">
    <h2>Subscribe to Access the Platform</h2>

    <?php
    if ($errors) foreach ($errors as $e) echo "<p class='error'>$e</p>";
    if ($success) echo "<p class='success'>$success</p>";
    ?>

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
</div>

</body>
</html>
