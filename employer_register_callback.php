<?php
session_start();
require_once('db_connect.php');
require_once('intasend_config.php');

if (!isset($_SESSION['employer_reg_data'])) {
    header("Location: employer_register.php");
    exit();
}

$reg_data = $_SESSION['employer_reg_data'];
$errors = [];
$success = '';
$payment_success = false;

$checkout_request_id = isset($_GET['checkout_request_id']) ? $_GET['checkout_request_id'] : null;

if ($checkout_request_id) {
    // Verify payment with IntaSend API using public key
    $ch = curl_init("https://api.intasend.com/api/v1/checkout/" . urlencode($checkout_request_id) . "/");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . INTASEND_PUBLISHABLE_KEY,
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response && $http_code == 200) {
        $result = json_decode($response, true);
        
        // Check if payment is complete
        if (isset($result['status']) && strtolower($result['status']) === 'complete') {
            $payment_success = true;
        } else {
            $errors[] = "Payment not completed. Status: " . ($result['status'] ?? 'unknown');
        }
    } else {
        $errors[] = "Unable to verify payment. Please contact support if payment was deducted.";
    }
} else {
    $errors[] = "No payment reference found. Please try again.";
}

if ($payment_success) {
    try {
        // Insert employer record
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
            // Clear session data
            unset($_SESSION['employer_reg_data']);
            $success = true;
        } else {
            $errors[] = "Registration failed. Please contact support.";
        }
    } catch (Exception $e) {
        $errors[] = "Database error occurred. Please contact support.";
        error_log("Employer registration error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <title>Registration Status - Homeworker Connect</title>
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
            text-align: center;
        }
        .success-icon {
            font-size: 4rem;
            color: #2e7d32;
            margin-bottom: 16px;
        }
        .error-icon {
            font-size: 4rem;
            color: #c0392b;
            margin-bottom: 16px;
        }
        .success-message {
            background: #e6f4ea;
            color: #2e7d32;
            padding: 16px;
            border-radius: 8px;
            margin: 16px 0;
            font-weight: 500;
        }
        .error-message {
            background: #ffeaea;
            color: #c0392b;
            padding: 16px;
            border-radius: 8px;
            margin: 16px 0;
            font-weight: 500;
        }
        .btn {
            background: linear-gradient(135deg, #197b88, #1ec8c8);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 16px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: linear-gradient(135deg, #156b75, #1ab5b5);
        }
        .countdown {
            color: #666;
            font-size: 0.9rem;
            margin-top: 16px;
        }
    </style>
    <?php if ($success): ?>
    <script>
        // Auto-redirect to login after 5 seconds
        let countdown = 5;
        function updateCountdown() {
            document.getElementById('countdown').textContent = countdown;
            countdown--;
            if (countdown < 0) {
                window.location.href = 'employer_login.php';
            }
        }
        
        // Start countdown when page loads
        window.onload = function() {
            updateCountdown();
            setInterval(updateCountdown, 1000);
        };
    </script>
    <?php endif; ?>
</head>
<body style="background: #f4f8fb; font-family: 'Segoe UI', Arial, sans-serif; display: flex; flex-direction: column; min-height: 100vh;">

    <div style="width:100%;text-align:center;margin:0;padding:0;">
        <img src="bghse.png" alt="Logo" style="height:48px;display:inline-block;margin:0 auto 0 auto;padding-top:8px;">
    </div>

    <div class="form-container">
        <?php if ($success): ?>
            <div class="success-icon">✅</div>
            <h2 style="color: #197b88; margin: 0 0 16px 0;">Registration Successful!</h2>
            <div class="success-message">
                <p style="margin: 0;">Welcome to Homeworker Connect!</p>
                <p style="margin: 8px 0 0 0;">Your employer account has been created successfully.</p>
            </div>
            <p style="color: #666; margin: 16px 0;">You can now login and start posting jobs.</p>
            <a href="employer_login.php" class="btn">Go to Login</a>
            <div class="countdown">
                Redirecting to login in <span id="countdown">5</span> seconds...
            </div>
        <?php else: ?>
            <div class="error-icon">❌</div>
            <h2 style="color: #c0392b; margin: 0 0 16px 0;">Registration Failed</h2>
            <?php foreach ($errors as $error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
            <a href="employer_register_payment.php" class="btn">Retry Payment</a>
            <p style="margin-top: 16px;">
                <a href="employer_register.php" style="color: #197b88; text-decoration: none;">← Back to Registration</a>
            </p>
        <?php endif; ?>
    </div>

    <footer style="margin-top: auto; text-align: center; color: #888; padding: 16px 0;">
        <p>&copy; <?= date("Y") ?> KenPro. All rights reserved.</p>
    </footer>

</body>
</html>