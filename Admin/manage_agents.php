<?php
session_start();
require_once '../db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['admin_name'];
$success = '';
$error = '';

// Handle actions
if (isset($_POST['action']) && isset($_POST['agent_id'])) {
    $agent_id = $_POST['agent_id'];
    $action = $_POST['action'];
    
    switch ($action) {
        case 'delete':
            $stmt = $conn->prepare("DELETE FROM agents WHERE id = ?");
            if ($stmt->execute([$agent_id])) {
                $success = 'Agent deleted successfully';
            } else {
                $error = 'Failed to delete agent';
            }
            break;
    }
}

// Get all agents
$stmt = $conn->prepare("SELECT id, name, email, phone, created_at FROM agents ORDER BY created_at DESC");
$stmt->execute();
$agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Agents - Admin Dashboard</title>
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
        .agents-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .agents-table th,
        .agents-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .agents-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        .agents-table tr:hover {
            background: #f8f9fa;
        }
        .btn-action {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-right: 5px;
        }
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        .btn-delete:hover {
            background: #c0392b;
        }
        .agent-id {
            font-weight: bold;
            color: #3498db;
            font-family: monospace;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .empty-state h4 {
            margin-bottom: 10px;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Manage Agents</h1>
            <p>View and manage registered agents</p>
        </div>

        <div class="admin-nav">
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="manage_agents.php" class="active">Manage Agents</a></li>
                <li><a href="manage_codes.php">Registration Codes</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="logout.php" style="color: #e74c3c;">Logout</a></li>
            </ul>
        </div>

        <?php if ($success): ?>
            <p class="success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;"><?= $success ?></p>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <p class="error" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;"><?= $error ?></p>
        <?php endif; ?>

        <div class="content-section">
            <h3>Registered Agents (<?= count($agents) ?>)</h3>
            
            <?php if (count($agents) > 0): ?>
                <table class="agents-table">
                    <thead>
                        <tr>
                            <th>Agent ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agents as $agent): ?>
                            <tr>
                                <td class="agent-id"><?= htmlspecialchars($agent['id']) ?></td>
                                <td><strong><?= htmlspecialchars($agent['name']) ?></strong></td>
                                <td><?= htmlspecialchars($agent['email']) ?></td>
                                <td><?= htmlspecialchars($agent['phone']) ?></td>
                                <td><?= date('M j, Y', strtotime($agent['created_at'])) ?></td>
                                <td>
                                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this agent?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="agent_id" value="<?= $agent['id'] ?>">
                                        <button type="submit" class="btn-action btn-delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h4>No Agents Registered</h4>
                    <p>No agents have registered yet. Agents will appear here once they complete registration.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 