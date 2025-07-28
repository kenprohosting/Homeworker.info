<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Hardcoded admin credentials
        if ($username === 'Admnr' && $password === 'Kenya@254') {
            $_SESSION['admin_id'] = 1;
            $_SESSION['admin_name'] = 'Administrator';
            $_SESSION['admin_email'] = 'support@homeworker.info';
            header("Location: index.php");
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta charset="UTF-8">
    <title>Admin Login - Homeworker Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .admin-login-container {
            max-width: 400px;
            width: 90%;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .admin-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .admin-header h2 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        .admin-header p {
            color: #666;
            font-size: 1rem;
        }
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-section img {
            height: 50px;
            margin-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        .btn-admin-login {
            width: 100%;
            background: #3498db;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }
        .btn-admin-login:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        .demo-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 25px;
            border-left: 4px solid #3498db;
        }
        .demo-info h4 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        .demo-info p {
            margin: 8px 0;
            font-size: 0.95rem;
            color: #666;
        }
        .demo-info .credentials {
            background: #e8f4fd;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .warning {
            font-size: 0.85rem;
            color: #e74c3c;
            margin-top: 10px;
            font-weight: bold;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #3498db;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="logo-section">
            <img src="../bghse.png" alt="Logo">
            <h2>Admin Access</h2>
        </div>
        
        <div class="admin-header">
            <h2>Admin Login</h2>
            <p>Access the agent management system</p>
        </div>
        
        <?php if ($error): ?>
            <p class="error" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="btn-admin-login">Login as Admin</button>
        </form>
        


        <div class="back-link">
            <a href="../index.php">← Back to Main Site</a>
        </div>
    </div>
</body>
</html> 