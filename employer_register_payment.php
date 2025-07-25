<?php
session_start();
require_once('intasend_config.php');

if (!isset($_SESSION['employer_reg_data'])) {
    header("Location: employer_register.php");
    exit();
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
        .form-container {
            max-width: 400px;
            margin: 40px auto 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            padding: 24px;
        }
        .payment-button {
            background: linear-gradient(135deg, #197b88, #1ec8c8);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 16px 24px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
            margin-top: 16px;
        }
        .payment-button:hover {
            background: linear-gradient(135deg, #156b75, #1ab5b5);
        }
        .payment-button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body style="background: #f4f8fb; font-family: 'Segoe UI', Arial, sans-serif; display: flex; flex-direction: column; min-height: 100vh;">

    <div style="width:100%;text-align:center;margin:0;padding:0;">
        <img src="bghse.png" alt="Logo" style="height:48px;display:inline-block;margin:0 auto 0 auto;padding-top:8px;">
    </div>

    <div class="form-container">
        <a href="employer_register.php" style="color: #197b88; text-decoration: none; font-weight: 500;">&larr; Back</a>
        <h2 style="text-align: center; color: #197b88; margin: 16px 0; font-size: 1.5rem;">Complete Your Registration</h2>
        <p style="text-align: center; color: #666; margin-bottom: 24px;">Pay <?= PAYMENT_CURRENCY ?> <?= EMPLOYER_REGISTRATION_FEE ?> to activate your employer account</p>
        
        <div style="background: #f8f9fa; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 8px 0; color: #197b88;">Registration Details:</h4>
            <p style="margin: 4px 0; color: #666;"><strong>Name:</strong> <?= htmlspecialchars($reg_data['name']) ?></p>
            <p style="margin: 4px 0; color: #666;"><strong>Email:</strong> <?= htmlspecialchars($reg_data['email']) ?></p>
            <p style="margin: 4px 0; color: #666;"><strong>Country:</strong> <?= htmlspecialchars($reg_data['country']) ?></p>
        </div>

        <button
            id="intasend-button"
            class="intaSendPayButton payment-button"
            data-amount="<?= EMPLOYER_REGISTRATION_FEE ?>"
            data-currency="<?= PAYMENT_CURRENCY ?>"
            data-email="<?= htmlspecialchars($reg_data['email']) ?>"
            data-first_name="<?= htmlspecialchars(explode(' ', $reg_data['name'])[0]) ?>"
            data-last_name="<?= htmlspecialchars(substr($reg_data['name'], strpos($reg_data['name'], ' ') + 1)) ?>"
            data-country="<?= htmlspecialchars($reg_data['country']) ?>"
            data-description="Employer registration fee - Homeworker Connect"
            data-redirect_url="<?= (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) ?>/employer_register_callback.php">
            <span id="button-text">Pay <?= PAYMENT_CURRENCY ?> <?= EMPLOYER_REGISTRATION_FEE ?> with IntaSend</span>
            <span id="loading-text" style="display: none;">Processing...</span>
        </button>

        <div id="debug-info" style="margin-top: 20px; padding: 10px; background: #f0f0f0; border-radius: 5px; font-size: 0.8rem; color: #666;">
            <strong>Debug Info:</strong><br>
            Public Key: <?= INTASEND_PUBLISHABLE_KEY ?><br>
            Live Mode: <?= INTASEND_LIVE_MODE ? 'Yes' : 'No' ?><br>
            Amount: <?= EMPLOYER_REGISTRATION_FEE ?> <?= PAYMENT_CURRENCY ?><br>
            <span id="sdk-status">SDK Status: Loading...</span>
        </div>

        <p style="text-align: center; margin-top: 16px; font-size: 0.9rem; color: #666;">
            Secure payment powered by IntaSend
        </p>
    </div>

    <script src="https://unpkg.com/intasend-inlinejs-sdk@4.0.1/build/intasend-inline.js"></script>
    <script>
        console.log('IntaSend SDK loaded');
        
        const button = document.getElementById('intasend-button');
        const buttonText = document.getElementById('button-text');
        const loadingText = document.getElementById('loading-text');

        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing IntaSend');
            document.getElementById('sdk-status').textContent = 'SDK Status: Initializing...';
            
            // Check if IntaSend is available
            if (typeof window.IntaSend === 'undefined') {
                console.error('IntaSend SDK not loaded');
                document.getElementById('sdk-status').textContent = 'SDK Status: Failed to load';
                alert('Payment system not available. Please refresh the page.');
                return;
            }
            
            try {
                const intasend = new window.IntaSend({
                    publicAPIKey: "<?= INTASEND_PUBLISHABLE_KEY ?>",
                    live: <?= INTASEND_LIVE_MODE ? 'true' : 'false' ?>
                });

                console.log('IntaSend initialized successfully');
                document.getElementById('sdk-status').textContent = 'SDK Status: Ready';

                intasend.on("COMPLETE", (results) => {
                    console.log('Payment completed:', results);
                    buttonText.style.display = 'none';
                    loadingText.style.display = 'inline';
                    loadingText.textContent = 'Payment successful! Redirecting...';
                    
                    setTimeout(() => {
                        window.location.href = "employer_register_callback.php?checkout_request_id=" + results.checkout_request_id;
                    }, 1000);
                });

                intasend.on("FAILED", (results) => {
                    console.log('Payment failed:', results);
                    alert("Payment failed. Please try again.");
                    button.disabled = false;
                    buttonText.style.display = 'inline';
                    loadingText.style.display = 'none';
                });

                intasend.on("IN-PROGRESS", (results) => {
                    console.log('Payment in progress:', results);
                    button.disabled = true;
                    buttonText.style.display = 'none';
                    loadingText.style.display = 'inline';
                    loadingText.textContent = 'Processing payment...';
                });

                // Add click event listener for debugging
                button.addEventListener('click', function() {
                    console.log('Payment button clicked');
                    document.getElementById('sdk-status').textContent = 'SDK Status: Button clicked, processing...';
                });

            } catch (error) {
                console.error('Error initializing IntaSend:', error);
                document.getElementById('sdk-status').textContent = 'SDK Status: Initialization failed';
                alert('Payment system initialization failed. Please refresh the page and try again.');
            }
        });
    </script>

    <footer style="margin-top: auto; text-align: center; color: #888; padding: 16px 0;">
        <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
    </footer>

</body>
</html>