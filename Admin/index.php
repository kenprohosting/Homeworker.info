<?php
session_start();
require_once '../db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['admin_name'];

// Get statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total_agents FROM agents");
$stmt->execute();
$total_agents = $stmt->fetch(PDO::FETCH_ASSOC)['total_agents'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_codes FROM agent_registration_codes");
$stmt->execute();
$total_codes = $stmt->fetch(PDO::FETCH_ASSOC)['total_codes'];

$stmt = $conn->prepare("SELECT COUNT(*) as active_codes FROM agent_registration_codes WHERE status = 'active'");
$stmt->execute();
$active_codes = $stmt->fetch(PDO::FETCH_ASSOC)['active_codes'];

$stmt = $conn->prepare("SELECT COUNT(*) as used_codes FROM agent_registration_codes WHERE status = 'used'");
$stmt->execute();
$used_codes = $stmt->fetch(PDO::FETCH_ASSOC)['used_codes'];

// Get recent agents
$stmt = $conn->prepare("SELECT id, name, email, phone, created_at FROM agents ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent codes
$stmt = $conn->prepare("SELECT code, agent_id, status, created_at FROM agent_registration_codes ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta charset="UTF-8">
    <title>Admin Dashboard - Homeworker Connect</title>
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #3498db;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 10px;
        }
        .stat-label {
            color: #666;
            font-size: 1.1rem;
        }
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
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
        .item-list {
            list-style: none;
            padding: 0;
        }
        .item-list li {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .item-list li:last-child {
            border-bottom: none;
        }
        .item-info h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }
        .item-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-used {
            background: #cce5ff;
            color: #004085;
        }
        .welcome-message {
            background: #e8f4fd;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <p>Welcome back, <?= htmlspecialchars($admin_name) ?>!</p>
        </div>

        <div class="admin-nav">
            <ul>
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="manage_agents.php">Manage Agents</a></li>
                <li><a href="manage_employees.php">Manage Employees</a></li>
                <li><a href="manage_employers.php">Manage Employers</a></li>
                <li><a href="manage_codes.php">Registration Codes</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="logout.php" style="color: #e74c3c;">Logout</a></li>
            </ul>
        </div>

        <div class="welcome-message">
            <h3>Quick Overview</h3>
            <p>Monitor your agent registration system, manage codes, and track platform activity from this central dashboard.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $total_agents ?></div>
                <div class="stat-label">Registered Agents</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $total_codes ?></div>
                <div class="stat-label">Total Codes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $active_codes ?></div>
                <div class="stat-label">Active Codes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $used_codes ?></div>
                <div class="stat-label">Used Codes</div>
            </div>
        </div>

        <div class="content-grid">
            <div class="content-section">
                <h3>Recent Agents</h3>
                <?php if (count($recent_agents) > 0): ?>
                    <ul class="item-list">
                        <?php foreach ($recent_agents as $agent): ?>
                            <li>
                                <div class="item-info">
                                    <h4><?= htmlspecialchars($agent['name']) ?></h4>
                                    <p>ID: <?= htmlspecialchars($agent['id']) ?> | <?= htmlspecialchars($agent['email']) ?></p>
                                    <p>Registered: <?= date('M j, Y', strtotime($agent['created_at'])) ?></p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No agents registered yet.</p>
                <?php endif; ?>
            </div>

            <div class="content-section">
                <h3>Recent Registration Codes</h3>
                <?php if (count($recent_codes) > 0): ?>
                    <ul class="item-list">
                        <?php foreach ($recent_codes as $code): ?>
                            <li>
                                <div class="item-info">
                                    <h4><?= htmlspecialchars($code['code']) ?></h4>
                                    <p>Agent ID: <?= htmlspecialchars($code['agent_id']) ?></p>
                                    <p>Created: <?= date('M j, Y', strtotime($code['created_at'])) ?></p>
                                </div>
                                <span class="status-badge status-<?= $code['status'] ?>">
                                    <?= ucfirst($code['status']) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No registration codes found.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="content-section" style="margin-top: 30px;">
            <h3>Quick Actions</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <a href="manage_codes.php?action=add" class="btn" style="text-align: center; padding: 15px; text-decoration: none;">
                    ➕ Add New Code
                </a>
                <a href="manage_agents.php" class="btn" style="text-align: center; padding: 15px; text-decoration: none;">
                    👥 View All Agents
                </a>
                <a href="reports.php" class="btn" style="text-align: center; padding: 15px; text-decoration: none;">
                    📊 Generate Reports
                </a>
                <a href="settings.php" class="btn" style="text-align: center; padding: 15px; text-decoration: none;">
                    ⚙️ System Settings
                </a>
            </div>
        </div>
    </div>
</body>
</html> 