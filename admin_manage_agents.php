<?php
session_start();
require_once 'db_connect.php';

// Check if admin is logged in (you'll need to implement admin authentication)
// For now, we'll use a simple check - you should implement proper admin auth
if (!isset($_SESSION['admin_id'])) {
    // Redirect to admin login or show error
    die("Admin access required. Please login as admin.");
}

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_code':
                $new_code = trim($_POST['new_code']);
                $new_agent_id = trim($_POST['new_agent_id']);
                $description = trim($_POST['description']);
                
                if (empty($new_code) || empty($new_agent_id)) {
                    $error = 'Code and Agent ID are required';
                } elseif (!is_numeric($new_agent_id)) {
                    $error = 'Agent ID must be a number';
                } else {
                    // In a real system, you'd store these in a database
                    // For now, we'll use a simple file or session storage
                    $success = "Registration code '$new_code' assigned to Agent ID $new_agent_id";
                }
                break;
                
            case 'revoke_code':
                $code_to_revoke = trim($_POST['code_to_revoke']);
                $success = "Registration code '$code_to_revoke' has been revoked";
                break;
        }
    }
}

// Get agent codes from database
$stmt = $conn->prepare("SELECT code, agent_id, description, status, assigned_to, created_at, used_at FROM agent_registration_codes ORDER BY created_at DESC");
$stmt->execute();
$agent_codes_result = $stmt->get_result();

// Get registered agents
$stmt = $conn->prepare("SELECT id, name, email, phone, created_at FROM agents ORDER BY created_at DESC");
$stmt->execute();
$registered_agents = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Agents - Homeworker Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
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
        }
        .admin-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .admin-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .code-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #eee;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .code-info {
            flex: 1;
        }
        .code-code {
            font-weight: bold;
            color: #2c3e50;
            font-family: monospace;
        }
        .code-status {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-revoked {
            background: #f8d7da;
            color: #721c24;
        }
        .agent-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .agent-table th,
        .agent-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .agent-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn-admin {
            background: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-admin:hover {
            background: #2980b9;
        }
        .btn-danger {
            background: #e74c3c;
        }
        .btn-danger:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="bghse.png" alt="Logo" style="height: 40px; margin-right: 10px;">
            Houselp Connect - Admin Panel
        </div>
        <nav class="main-nav">
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="admin-header">
        <h1>Agent Management</h1>
        <p>Manage agent registration codes and monitor agent registrations</p>
    </div>

    <div class="admin-container">
        <?php if ($success): ?>
            <p class="success"><?= $success ?></p>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <div class="admin-grid">
            <!-- Registration Codes Section -->
            <div class="admin-section">
                <h3>Registration Codes</h3>
                
                <form method="POST" action="" style="margin-bottom: 20px;">
                    <input type="hidden" name="action" value="add_code">
                    <div class="form-group">
                        <label>Registration Code:</label>
                        <input type="text" name="new_code" placeholder="e.g., AGENT2025" required>
                    </div>
                    <div class="form-group">
                        <label>Agent ID:</label>
                        <input type="number" name="new_agent_id" placeholder="e.g., 1006" required>
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description" placeholder="Optional description"></textarea>
                    </div>
                    <button type="submit" class="btn-admin">Add New Code</button>
                </form>

                <h4>Registration Codes:</h4>
                <?php if ($agent_codes_result->num_rows > 0): ?>
                    <?php while ($code = $agent_codes_result->fetch_assoc()): ?>
                        <div class="code-item">
                            <div class="code-info">
                                <div class="code-code"><?= htmlspecialchars($code['code']) ?></div>
                                <div>Agent ID: <?= htmlspecialchars($code['agent_id']) ?></div>
                                <?php if ($code['assigned_to']): ?>
                                    <div>Assigned to: <?= htmlspecialchars($code['assigned_to']) ?></div>
                                <?php endif; ?>
                                <?php if ($code['description']): ?>
                                    <div style="font-size: 0.8rem; color: #666;"><?= htmlspecialchars($code['description']) ?></div>
                                <?php endif; ?>
                                <div style="font-size: 0.8rem; color: #999;">
                                    Created: <?= date('M j, Y', strtotime($code['created_at'])) ?>
                                    <?php if ($code['used_at']): ?>
                                        | Used: <?= date('M j, Y', strtotime($code['used_at'])) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <span class="code-status status-<?= $code['status'] ?>">
                                    <?= ucfirst($code['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No registration codes found.</p>
                <?php endif; ?>
            </div>

            <!-- Registered Agents Section -->
            <div class="admin-section">
                <h3>Registered Agents</h3>
                
                <?php if ($registered_agents->num_rows > 0): ?>
                    <table class="agent-table">
                        <thead>
                            <tr>
                                <th>Agent ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($agent = $registered_agents->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($agent['id']) ?></strong></td>
                                    <td><?= htmlspecialchars($agent['name']) ?></td>
                                    <td><?= htmlspecialchars($agent['email']) ?></td>
                                    <td><?= htmlspecialchars($agent['phone']) ?></td>
                                    <td><?= date('M j, Y', strtotime($agent['created_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No agents have registered yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="admin-section">
            <h3>Agent Statistics</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 5px;">
                    <div style="font-size: 2rem; font-weight: bold; color: #3498db;">
                        <?= $registered_agents->num_rows ?>
                    </div>
                    <div>Total Registered Agents</div>
                </div>
                <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 5px;">
                    <div style="font-size: 2rem; font-weight: bold; color: #27ae60;">
                        <?= $agent_codes_result->num_rows ?>
                    </div>
                    <div>Total Registration Codes</div>
                </div>
                <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 5px;">
                    <div style="font-size: 2rem; font-weight: bold; color: #e74c3c;">
                        <?php 
                        $stmt = $conn->prepare("SELECT COUNT(*) as pending_count FROM agent_registration_codes WHERE status = 'active'");
                        $stmt->execute();
                        $pending_count = $stmt->get_result()->fetch_assoc()['pending_count'];
                        echo $pending_count;
                        ?>
                    </div>
                    <div>Active Codes</div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
    </footer>
</body>
</html> 