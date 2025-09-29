<?php
session_start();
require_once('intasend_config.php');

if (!isset($_SESSION['employer_reg_data'])) {
    header("Location: employer_register.php");
    exit();
}

// Handle AJAX request from SDK COMPLETE event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_paid') {
    $_SESSION['payment_completed'] = true;
    exit; // stop execution so no HTML is sent
}

$reg_data = $_SESSION['employer_reg_data'];
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <title>Employer Registration Payment - Homeworker Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <style>
        .form-container { max-width: 400px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 24px; }
        .payment-button { background: linear-gradient(135deg,#197b88,#1ec8c8); color:#fff; border:none; border-radius:8px; padding:16px 24px; font-size:1.1rem; font-weight:600; cursor:pointer; width:100%; transition:background 0.3s; margin-top:16px; }
        .payment-button:hover { background: linear-gradient(135deg,#156b75,#1ab5b5); }
        .form-header { text-align:center; color:#197b88; margin:16px 0; font-size:1.5rem; }
        .details-box { background:#f8f9fa; padding:16px; border-radius:8px; margin-bottom:20px; }
        .details-box p { margin:4px 0; color:#666; }
        .back-link { color:#197b88; text-decoration:none; font-weight:500; }
    </style>
</head>
<body style="background:#f4f8fb; font-family:'Segoe UI',Arial,sans-serif; display:flex; flex-direction:column; min-height:100vh;">

<div style="width:100%;text-align:center;margin:0;padding:0;">
    <img src="bghse.png" alt="Logo" style="height:48px;display:inline-block;margin:0 auto;padding-top:8px;">
</div>

<div class="form-container">
    <a href="employer_register.php" class="back-link">&larr; Back</a>
    <h2 class="form-header">Complete Your Registration</h2>
    <p style="text-align:center;color:#666;margin-bottom:24px;">
        Pay <?= PAYMENT_CURRENCY ?> <?= EMPLOYER_REGISTRATION_FEE ?> to activate your employer account
    </p>

    <div class="details-box">
        <h4 style="margin:0 0 8px 0; color:#197b88;">Registration Details:</h4>
        <p><strong>Name:</strong> <?= htmlspecialchars($reg_data['name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($reg_data['email']) ?></p>
        <p><strong>Country:</strong> <?= htmlspecialchars($reg_data['country']) ?></p>
    </div>

    <button
        id="intasend-button"
        class="intaSendPayButton payment-button"
        data-amount="<?= EMPLOYER_REGISTRATION_FEE ?>"
        data-currency="<?= PAYMENT_CURRENCY ?>"
        data-email="<?= htmlspecialchars($reg_data['email']) ?>"
        data-description="Employer registration fee - Homeworker Connect">
        Pay <?= PAYMENT_CURRENCY ?> <?= EMPLOYER_REGISTRATION_FEE ?> with IntaSend
    </button>
</div>

<script src="https://unpkg.com/intasend-inlinejs-sdk@4.0.1/build/intasend-inline.js"></script>
<script>
    const button = document.getElementById('intasend-button');

    const intasend = new window.IntaSend({
        publicAPIKey: "<?= INTASEND_PUBLISHABLE_KEY ?>",
        live: <?= INTASEND_LIVE_MODE ? 'true' : 'false' ?>
    });

    intasend.on("COMPLETE", () => {
        // Mark payment completed in session
        fetch('employer_register_payment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=mark_paid'
        }).then(() => {
            // Redirect to callback page to create employer
            window.location.href = "employer_register_callback.php";
        });
    });

    intasend.on("FAILED", () => {
        alert("Payment failed. Please try again.");
        button.disabled = false;
        button.textContent = "Pay <?= PAYMENT_CURRENCY ?> <?= EMPLOYER_REGISTRATION_FEE ?> with IntaSend";
    });

    intasend.on("IN-PROGRESS", () => {
        button.disabled = true;
        button.textContent = "Processing payment...";
    });

    button.addEventListener("click", () => {
        button.disabled = true;
        button.textContent = "Initializing payment...";
    });
</script>

<footer style="margin-top:auto;text-align:center;color:#888;padding:16px 0;">
    <p>&copy; <?= date("Y") ?> KenPro. All rights reserved.</p>
</footer>

</body>
</html>