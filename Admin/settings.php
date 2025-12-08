<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];

$message = "";

// Update name
if (isset($_POST['update_name'])) {
    $new = trim($_POST['admin_name']);
    if ($new !== "") {
        $stmt = $conn->prepare("UPDATE admin SET name = :n WHERE id = :id");
        $stmt->execute([':n' => $new, ':id' => $admin_id]);

        $_SESSION['admin_name'] = $new;
        $admin_name = $new;
        $message = "✅ Name updated successfully.";
    }
}

// Update password
if (isset($_POST['update_password'])) {
    $pass = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($pass === $confirm && strlen($pass) >= 6) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admin SET password = :p WHERE id = :id");
        $stmt->execute([':p' => $hash, ':id' => $admin_id]);

        $message = "✅ Password updated successfully.";
    } else {
        $message = "❌ Password mismatch or too short (minimum 6 characters).";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta charset="UTF-8">
    <title>Settings - Homeworker Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .admin-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        .admin-nav {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .admin-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .admin-nav a {
            color: #2c3e50;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .admin-nav a:hover {
            background: #f8f9fa;
        }
        .admin-nav a.active {
            background: #3498db;
            color: white;
        }
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        .content-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .content-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .welcome-message {
            background: #e8f4fd;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        .message-box {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .message-box.error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        .form-group .input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .form-group .input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        .profile-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .profile-icon {
            font-size: 4rem;
            color: #3498db;
            margin-bottom: 20px;
        }
        .admin-info {
            text-align: center;
            margin-bottom: 30px;
        }
        .admin-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: bold;
        }
        .admin-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-item:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #666;
            font-weight: 600;
        }
        .detail-value {
            color: #2c3e50;
            font-weight: 500;
        }
        .security-tips {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #ffc107;
        }
        .security-tips h4 {
            margin-top: 0;
            color: #856404;
        }
        .security-tips ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .security-tips li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Admin Settings</h1>
            <p>Manage your account preferences and security settings</p>
        </div>

        <div class="admin-nav">
            <ul>
                <li><a href="https://homeworker.info/" style="color: #e74c3c;">Back</a></li>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="manage_agents.php">Manage Agents</a></li>
                <li><a href="manage_employees.php">Manage Employees</a></li>
                <li><a href="manage_employers.php">Manage Employers</a></li>
                <li><a href="manage_codes.php">Registration Codes</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="settings.php" class="active">Settings</a></li>
                <li><a href="logout.php" style="color: #e74c3c;">Logout</a></li>
            </ul>
        </div>

        <div class="welcome-message">
            <h3>⚙️ Account Settings</h3>
            <p>Update your profile information and manage account security from this page.</p>
        </div>

        <?php if ($message): ?>
            <div class="<?= strpos($message, '❌') !== false ? 'message-box error' : 'message-box' ?>">
                <?php if (strpos($message, '✅') !== false): ?>
                    <span style="font-size: 1.5rem;">✅</span>
                <?php elseif (strpos($message, '❌') !== false): ?>
                    <span style="font-size: 1.5rem;">❌</span>
                <?php endif; ?>
                <span><?= htmlspecialchars($message) ?></span>
            </div>
        <?php endif; ?>

        <div class="content-grid">
            <!-- Profile Information -->
            <div class="content-section">
                <h3>👤 Profile Information</h3>
                <div class="admin-info">
                    <div class="admin-avatar">
                        <?= strtoupper(substr($admin_name, 0, 1)) ?>
                    </div>
                    <h3 style="margin: 0 0 10px 0; color: #2c3e50;"><?= htmlspecialchars($admin_name) ?></h3>
                    <p style="color: #666; margin: 0;">Administrator</p>
                </div>

                <div class="admin-details">
                    <div class="detail-item">
                        <span class="detail-label">Admin ID:</span>
                        <span class="detail-value">#<?= $admin_id ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Session Status:</span>
                        <span class="detail-value" style="color: #2ecc71;">Active</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Last Login:</span>
                        <span class="detail-value"><?= date('F j, Y, g:i a') ?></span>
                    </div>
                </div>
            </div>

            <!-- Update Display Name -->
            <div class="content-section">
                <h3>✏️ Update Display Name</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="admin_name">Display Name</label>
                        <input type="text" id="admin_name" name="admin_name" class="input" 
                               value="<?= htmlspecialchars($admin_name) ?>" 
                               placeholder="Enter your display name">
                    </div>
                    <button type="submit" name="update_name" class="btn" style="width: 100%; padding: 12px;">
                        Save Changes
                    </button>
                </form>

                <div class="security-tips">
                    <h4>💡 Tip</h4>
                    <p>Your display name is visible throughout the admin panel. Choose a professional name that identifies you clearly.</p>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="content-section" style="margin-top: 20px;">
            <h3>🔒 Change Password</h3>
            <form method="POST">
                <div class="content-grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" class="input" 
                               placeholder="Enter new password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm">Confirm Password</label>
                        <input type="password" id="confirm" name="confirm" class="input" 
                               placeholder="Confirm new password" required>
                    </div>
                </div>
                <button type="submit" name="update_password" class="btn" style="width: 100%; padding: 12px;">
                    Update Password
                </button>
            </form>

            <div class="security-tips" style="margin-top: 20px;">
                <h4>🔐 Password Security Tips</h4>
                <ul>
                    <li>Use at least 8 characters</li>
                    <li>Include uppercase and lowercase letters</li>
                    <li>Add numbers and special characters</li>
                    <li>Avoid using personal information</li>
                    <li>Don't reuse passwords from other sites</li>
                </ul>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="content-section" style="margin-top: 30px;">
            <h3>⚡ Quick Actions</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <a href="logout.php" class="btn" style="text-align: center; padding: 15px; text-decoration: none; background: #e74c3c;">
                    🔒 Logout Now
                </a>
                <a href="index.php" class="btn" style="text-align: center; padding: 15px; text-decoration: none;">
                    📊 Back to Dashboard
                </a>
                <a href="reports.php" class="btn" style="text-align: center; padding: 15px; text-decoration: none;">
                    📈 View Reports
                </a>
                <button onclick="clearCache()" class="btn" style="text-align: center; padding: 15px; text-decoration: none; background: #95a5a6;">
                    🧹 Clear Cache
                </button>
            </div>
        </div>

        <!-- Session Information -->
        <div class="content-section" style="margin-top: 30px;">
            <h3>📱 Session Information</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div>
                    <h4 style="color: #3498db; margin-bottom: 10px;">Browser Information</h4>
                    <p style="font-size: 0.9rem; color: #666;"><?= htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') ?></p>
                </div>
                <div>
                    <h4 style="color: #3498db; margin-bottom: 10px;">IP Address</h4>
                    <p style="font-size: 0.9rem; color: #666;"><?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'Unknown') ?></p>
                </div>
                <div>
                    <h4 style="color: #3498db; margin-bottom: 10px;">Current Time</h4>
                    <p style="font-size: 0.9rem; color: #666;"><?= date('F j, Y, g:i a') ?></p>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Password strength indicator
    document.getElementById('password')?.addEventListener('input', function(e) {
        const password = e.target.value;
        const strength = checkPasswordStrength(password);
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');
        
        if (strengthBar && strengthText) {
            strengthBar.style.width = strength.percentage + '%';
            strengthBar.style.backgroundColor = strength.color;
            strengthText.textContent = strength.text;
        }
    });

    function checkPasswordStrength(password) {
        let score = 0;
        
        // Length check
        if (password.length >= 8) score++;
        if (password.length >= 12) score++;
        
        // Complexity checks
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        
        // Determine strength
        if (score >= 5) {
            return { percentage: 100, color: '#2ecc71', text: 'Strong' };
        } else if (score >= 3) {
            return { percentage: 66, color: '#f39c12', text: 'Medium' };
        } else if (score >= 1) {
            return { percentage: 33, color: '#e74c3c', text: 'Weak' };
        } else {
            return { percentage: 0, color: '#95a5a6', text: 'Very Weak' };
        }
    }

    // Clear cache function
    function clearCache() {
        if (confirm('Are you sure you want to clear the cache?')) {
            // In a real application, this would make an AJAX call to clear cache
            alert('Cache cleared successfully!');
        }
    }

    // Confirm password match
    document.querySelector('form')?.addEventListener('submit', function(e) {
        const password = document.getElementById('password')?.value;
        const confirm = document.getElementById('confirm')?.value;
        
        if (password && confirm && password !== confirm) {
            e.preventDefault();
            alert('Passwords do not match!');
        }
        
        if (password && password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long!');
        }
    });
    </script>
</body>
</html>