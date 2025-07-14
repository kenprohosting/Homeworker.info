<?php
session_start();
require_once 'db_connect.php';

// Check if agent is logged in
if (!isset($_SESSION['agent_id'])) {
    header("Location: agent_login.php");
    exit();
}

$agent_id = $_SESSION['agent_id'];
$agent_name = $_SESSION['agent_name'];

// Get employee statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total_employees FROM employees WHERE agent_id = ?");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$total_employees = $stmt->get_result()->fetch_assoc()['total_employees'];

$stmt = $conn->prepare("SELECT COUNT(*) as active_employees FROM employees WHERE agent_id = ? AND status = 'active'");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$active_employees = $stmt->get_result()->fetch_assoc()['active_employees'];

$stmt = $conn->prepare("SELECT COUNT(*) as pending_employees FROM employees WHERE agent_id = ? AND status = 'pending'");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$pending_employees = $stmt->get_result()->fetch_assoc()['pending_employees'];

// Get recent employees
$stmt = $conn->prepare("SELECT id, name, email, phone, status, created_at FROM employees WHERE agent_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$recent_employees = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Dashboard - Homeworker Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #197b88 0%, #1ec8c8 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #197b88;
            margin-bottom: 10px;
        }
        .stat-label {
            color: #666;
            font-size: 1.1rem;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        .employees-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .employee-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .employee-item:last-child {
            border-bottom: none;
        }
        .employee-info h4 {
            margin: 0 0 5px 0;
            color: #197b88;
        }
        .employee-info p {
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
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .logout-btn:hover {
            background: #c82333;
        }
    </style>
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
                <li><a href="agent_dashboard.php">Dashboard</a></li>
                <li><a href="agent_logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="dashboard-header">
        <h1>Welcome, <?= htmlspecialchars($agent_name) ?>!</h1>
        <p>Agent ID: <?= htmlspecialchars($agent_id) ?> | Agent Dashboard - Manage Your Employees</p>
    </div>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $total_employees ?></div>
                <div class="stat-label">Total Employees</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $active_employees ?></div>
                <div class="stat-label">Active Employees</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $pending_employees ?></div>
                <div class="stat-label">Pending Approval</div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="register_employee.php" class="btn">Register New Employee</a>
            <a href="manage_employees.php" class="btn">Manage Employees</a>
            <a href="employee_reports.php" class="btn">View Reports</a>
        </div>

        <div class="employees-section">
            <h2>Recent Employees</h2>
            <?php if ($recent_employees->num_rows > 0): ?>
                <?php while ($employee = $recent_employees->fetch_assoc()): ?>
                    <div class="employee-item">
                        <div class="employee-info">
                            <h4><?= htmlspecialchars($employee['name']) ?></h4>
                            <p><?= htmlspecialchars($employee['email']) ?> | <?= htmlspecialchars($employee['phone']) ?></p>
                            <p>Registered: <?= date('M j, Y', strtotime($employee['created_at'])) ?></p>
                        </div>
                        <div>
                            <span class="status-badge status-<?= $employee['status'] ?>">
                                <?= ucfirst($employee['status']) ?>
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No employees registered yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
    </footer>
</body>
</html> 