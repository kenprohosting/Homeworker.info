<?php
session_start();

if (!isset($_SESSION['employer_reg_data'])) {
    header("Location: employer_register.php");
    exit();
}

$reg_data = $_SESSION['employer_reg_data'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employer Registration Payment</title>
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
    <h2>Pay KES 10 to Complete Registration</h2>
    <button
        id="intasend-button"
        class="intaSendPayButton"
        data-amount="10"
        data-currency="KES"
        data-email="<?= htmlspecialchars($reg_data['email']) ?>"
        data-description="Employer registration fee"
        data-redirect_url="http://localhost:8000/employer_register_callback.php">
        Pay KES 10 with IntaSend
    </button>
    <script src="https://unpkg.com/intasend-inlinejs-sdk@4.0.1/build/intasend-inline.js"></script>
    <script>
        new window.IntaSend({
            publicAPIKey: "ISPubKey_live_40f25458-716c-47c5-b049-786fd1f3a1ce", // Replace with your actual key
            live: true // set to false for sandbox
        })
        .on("COMPLETE", (results) => {
            window.location.href = "employer_register_callback.php?checkout_request_id=" + results.checkout_request_id;
        })
        .on("FAILED", (results) => { alert("Payment failed. Please try again."); })
        .on("IN-PROGRESS", (results) => { /* Optionally show progress */ });
    </script>
</div>

</body>
</html>