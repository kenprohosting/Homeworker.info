
<?php
session_start();
require_once 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $agent_id = trim($_POST['agent_id']);
    $registration_code = trim($_POST['registration_code']);
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $national_id = trim($_POST['national_id']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($agent_id) || empty($registration_code) || empty($name) || empty($phone) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif (empty($national_id)) {
        $error = 'National ID/Passport Number is required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!is_numeric($agent_id) || $agent_id <= 0) {
        $error = 'Agent ID must be a positive number';
    } elseif ($agent_id > 999999) {
        $error = 'Agent ID must be less than 1,000,000';
    } else {
        // Verify registration code from database
        $stmt = $conn->prepare("SELECT agent_id, status FROM agent_registration_codes WHERE code = ? AND status = 'active'");
        $stmt->execute([$registration_code]);
        $code_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$code_data) {
            $error = 'Invalid or inactive registration code. Please contact the company for authorization.';
        } else {
            $expected_agent_id = $code_data['agent_id'];
            
            if ($agent_id != $expected_agent_id) {
                $error = 'Agent ID does not match the registration code. Please use the correct ID.';
            } else {
                // Check if agent ID already exists
                $stmt = $conn->prepare("SELECT id FROM agents WHERE id = ?");
                $stmt->execute([$agent_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    $error = 'Agent ID already exists. Please contact the company for a new ID.';
                } else {
                    // Check if email already exists
                    $stmt = $conn->prepare("SELECT id FROM agents WHERE email = ?");
                    $stmt->execute([$email]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($result) {
                        $error = 'Email already registered';
                    } else {
                        // Hash password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Insert agent with specific ID
                        $stmt = $conn->prepare("INSERT INTO agents (id, name, phone, email, password) VALUES (?, ?, ?, ?, ?)");
                        $stmt = $conn->prepare("INSERT INTO agents (id, name, phone, email, national_id, password) VALUES (?, ?, ?, ?, ?, ?)");
                        $success_insert = $stmt->execute([$agent_id, $name, $phone, $email, $national_id, $hashed_password]);
                        
                        if ($success_insert) {
                            // Mark the registration code as used
                            $stmt = $conn->prepare("UPDATE agent_registration_codes SET status = 'used', used_at = CURRENT_TIMESTAMP WHERE code = ?");
                            $stmt->execute([$registration_code]);
                            
                            $success = 'Agent registered successfully! You can now login.';
                        } else {
                            $error = 'Registration failed. Please try again.';
                        }
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Registration - Homeworker Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
</head>
<body style="background: #f4f8fb; font-family: 'Segoe UI', Arial, sans-serif; display: flex; flex-direction: column; min-height: 100vh;">

    <div style="width:100%;text-align:center;margin:0;padding:0;">
        <img src="bghse.png" alt="Logo" style="height:48px;display:inline-block;margin:0 auto 0 auto;padding-top:8px;">
    </div>
    <div class="form-container" style="max-width: 360px; margin: 24px auto; background: #ffffff; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); padding: 24px; display: flex; flex-direction: column; gap: 16px;">
        <a href="index.php" style="color: #197b88; text-decoration: none; font-weight: 500; align-self: flex-start;">&larr; Back</a>
        <h2 style="text-align: center; color: #197b88; margin: 0; font-size: 1.5rem;">Agent Registration</h2>
        <p style="text-align: center; color: #666; margin: 0; font-size: 0.9rem;">
            Need to become an agent? <a href="agent_application.php" style="color: #197b88; text-decoration: none; font-weight: 500;">Click to Apply and get a Registration Code</a>
        </p>
        <?php if ($error): ?>
            <p style="background: #ffeaea; color: #c0392b; padding: 8px 12px; border-radius: 8px; margin: 0; text-align: center;"><?= $error ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p style="background: #e6f4ea; color: #2e7d32; padding: 8px 12px; border-radius: 8px; margin: 0; text-align: center;"><?= $success ?></p>
        <?php endif; ?>
<form method="POST" action="" style="display: flex; flex-direction: column; gap: 12px;">
    <input type="text" name="registration_code" placeholder="Registration Code" value="<?= isset($_POST['registration_code']) ? htmlspecialchars($_POST['registration_code']) : '' ?>" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
    <small style="color: #666; margin: -8px 0 0; font-size: 0.8rem;">Enter the registration code provided by the company</small>
    <!-- proceed button to show full form -->
    <button type="button" id="proceedBtn" onclick="showFullForm()" 
    style="display: none; background: #197b88; color: #fff; border: none; border-radius: 8px; padding: 12px; font-size: 1rem; font-weight: 600; cursor: pointer;">
    Proceed to Register
</button> 

<div id="hidden-section" style="display: none; flex-direction: column; gap: 12px;">
        <input type="number" name="agent_id" placeholder="Agent ID" value="<?= isset($_POST['agent_id']) ? htmlspecialchars($_POST['agent_id']) : '' ?>" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <small style="color: #666; margin: -8px 0 0; font-size: 0.8rem;">Enter the specific agent ID assigned to you</small>
        <input type="text" name="national_id" placeholder="National ID/Passport Number" value="<?= isset($_POST['national_id']) ? htmlspecialchars($_POST['national_id']) : '' ?>" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <input type="text" name="name" placeholder="Full Name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <input type="tel" name="phone" placeholder="Phone Number" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <input type="email" name="email" placeholder="Email Address" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <div style="position: relative;">
            <input type="password" name="password" id="password" placeholder="Password" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s; width: 100%; box-sizing: border-box;">
            <span onclick="togglePassword('password', this)" style="position: absolute; top: 50%; right: 12px; transform: translateY(-50%); cursor: pointer; font-size: 1.2em;">
                &#128065;
            </span>
        </div>
        <div style="position: relative;">
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s; width: 100%; box-sizing: border-box;">
            <span onclick="togglePassword('confirm_password', this)" style="position: absolute; top: 50%; right: 12px; transform: translateY(-50%); cursor: pointer; font-size: 1.2em;">
                &#128065;
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px; margin-top: 12px;">
            <input type="checkbox" name="terms" id="terms" required style="cursor: pointer;">
            <label for="terms" style="color: #666; font-size: 0.9rem;">I agree to the <a href="agent_terms_and_conditions.php" target="_blank" style="color: #197b88; text-decoration: none;">Agent Terms and Conditions</a></label>
        </div>
        <button type="submit" style="background: linear-gradient(135deg, #197b88, #1ec8c8); color: #fff; border: none; border-radius: 8px; padding: 12px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: background 0.3s;">Register as Agent</button>
    </div>
</form>
<p style="text-align: center; margin: 0; font-size: 0.9rem;">
    Already have an account? <a href="agent_login.php" style="color: #197b88; text-decoration: none;">Login here</a>
</p>

    </div>



    <footer style="margin-top: auto; text-align: center; color: #888; padding: 16px 0;">
        <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
    </footer>
<script>
function checkAgentFields() {
    const regCodeInput = document.querySelector('input[name="registration_code"]');
    const proceedBtn = document.getElementById('proceedBtn');
    const hiddenSection = document.getElementById('hidden-section');

    const regCode = regCodeInput.value.trim();

    if (regCode !== '') {
        proceedBtn.style.display = 'block';
    } else {
        proceedBtn.style.display = 'none';
        hiddenSection.style.display = 'none';
    }
}

function showFullForm() {
    const hiddenSection = document.getElementById('hidden-section');
    const proceedBtn = document.getElementById('proceedBtn');

    hiddenSection.style.display = 'flex'; // or 'block', depending on your layout
    proceedBtn.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const regCodeInput = document.querySelector('input[name="registration_code"]');
    regCodeInput.addEventListener('input', checkAgentFields);
    checkAgentFields(); // Initial check on page load
});

function togglePassword(id, el) {
    const input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text";
        el.innerHTML = "&#128064;";
    } else {
        input.type = "password";
        el.innerHTML = "&#128065;";
    }
}
</script>

</body>
</html>