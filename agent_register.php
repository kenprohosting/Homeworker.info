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
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($agent_id) || empty($registration_code) || empty($name) || empty($phone) || empty($email) || empty($password)) {
        $error = 'All fields are required';
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
        $stmt->bind_param("s", $registration_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            $error = 'Invalid or inactive registration code. Please contact the company for authorization.';
        } else {
            $code_data = $result->fetch_assoc();
            $expected_agent_id = $code_data['agent_id'];
            
            if ($agent_id != $expected_agent_id) {
                $error = 'Agent ID does not match the registration code. Please use the correct ID.';
            } else {
                // Check if agent ID already exists
                $stmt = $conn->prepare("SELECT id FROM agents WHERE id = ?");
                $stmt->bind_param("i", $agent_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error = 'Agent ID already exists. Please contact the company for a new ID.';
                } else {
                    // Check if email already exists
                    $stmt = $conn->prepare("SELECT id FROM agents WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $error = 'Email already registered';
                    } else {
                        // Hash password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Insert agent with specific ID
                        $stmt = $conn->prepare("INSERT INTO agents (id, name, phone, email, password) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("issss", $agent_id, $name, $phone, $email, $hashed_password);
                        
                        if ($stmt->execute()) {
                            // Mark the registration code as used
                            $stmt = $conn->prepare("UPDATE agent_registration_codes SET status = 'used', used_at = CURRENT_TIMESTAMP WHERE code = ?");
                            $stmt->bind_param("s", $registration_code);
                            $stmt->execute();
                            
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
<body>
    <header>
        <div class="logo">
            <img src="bghse.png" alt="Logo" style="height: 40px; margin-right: 10px;">
            Houselp Connect
        </div>
        <nav class="main-nav">
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="faq.php">FAQ</a></li>
            </ul>
        </nav>
    </header>

    <div class="form-container">
        <h2>Agent Registration</h2>
        <p style="text-align: center; color: #666; margin-bottom: 20px;">
            <strong>Authorized Agents Only</strong><br>
            You must have a valid registration code from the company to register as an agent.
        </p>
        
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <p class="success"><?= $success ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="registration_code" placeholder="Registration Code" value="<?= isset($_POST['registration_code']) ? htmlspecialchars($_POST['registration_code']) : '' ?>" required>
            <small style="color: #666; display: block; margin-bottom: 15px;">Enter the registration code provided by the company</small>
            
            <input type="number" name="agent_id" placeholder="Agent ID" value="<?= isset($_POST['agent_id']) ? htmlspecialchars($_POST['agent_id']) : '' ?>" required>
            <small style="color: #666; display: block; margin-bottom: 15px;">Enter the specific agent ID assigned to you</small>
            
            <input type="text" name="name" placeholder="Full Name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
            
            <input type="tel" name="phone" placeholder="Phone Number" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" required>
            
            <input type="email" name="email" placeholder="Email Address" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
            
            <input type="password" name="password" placeholder="Password" required>
            
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            
            <button type="submit" class="btn">Register as Agent</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">
            Already have an account? <a href="agent_login.php">Login here</a>
        </p>
        
        <p style="text-align: center; margin-top: 20px; color: #666; font-size: 0.9rem;">
            Need to become an agent? Contact the company at <strong>admin@househelp.info</strong>
        </p>
    </div>

    <footer>
        <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
    </footer>
</body>
</html> 