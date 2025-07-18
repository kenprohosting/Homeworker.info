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
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM employees WHERE agent_id = ?");
$stmt->execute([$agent_id]);
$total_employees = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as active FROM employees WHERE agent_id = ? AND status = 'active'");
$stmt->execute([$agent_id]);
$active_employees = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

$stmt = $conn->prepare("SELECT COUNT(*) as pending FROM employees WHERE agent_id = ? AND status = 'pending'");
$stmt->execute([$agent_id]);
$pending_employees = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];

$stmt = $conn->prepare("SELECT COUNT(*) as inactive FROM employees WHERE agent_id = ? AND status = 'inactive'");
$stmt->execute([$agent_id]);
$inactive_employees = $stmt->fetch(PDO::FETCH_ASSOC)['inactive'];

// Get employees by gender
$stmt = $conn->prepare("SELECT gender, COUNT(*) as count FROM employees WHERE agent_id = ? GROUP BY gender");
$stmt->execute([$agent_id]);
$gender_stats = $stmt;

// Get recent registrations
$stmt = $conn->prepare("SELECT name, email, status, created_at FROM employees WHERE agent_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$agent_id]);
$recent_registrations = $stmt;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Reports - Homeworker Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <style>
        .reports-header {
            background: linear-gradient(135deg, #197b88 0%, #1ec8c8 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            margin-bottom: 30px;
        }
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
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
        .report-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .gender-chart {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin: 20px 0;
        }
        .gender-item {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            background: #f8f9fa;
        }
        .gender-number {
            font-size: 2rem;
            font-weight: bold;
            color: #197b88;
        }
        .recent-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .recent-table th,
        .recent-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .recent-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #197b88;
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
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="bghse.png" alt="Logo" style="height: 40px; margin-right: 10px;">
            
        </div>
        <nav class="main-nav">
            <ul class="nav-links">
                <li><a href="agent_dashboard.php">Dashboard</a></li>
                <li><a href="manage_employees.php">Manage Employees</a></li>
                <li><a href="agent_logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="reports-header">
        <h1>Employee Reports</h1>
        <p>Agent: <?= htmlspecialchars($agent_name) ?></p>
        <p>Generated on: <?= date('F j, Y') ?></p>
    </div>

    <div class="container">
        <div class="stats-overview">
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
            <div class="stat-card">
                <div class="stat-number"><?= $inactive_employees ?></div>
                <div class="stat-label">Inactive Employees</div>
            </div>
        </div>

        <div class="report-section">
            <h2>Gender Distribution</h2>
            <div class="gender-chart">
                <?php 
                $male_count = 0;
                $female_count = 0;
                while ($row = $gender_stats->fetch(PDO::FETCH_ASSOC)) {
                    if ($row['gender'] == 'male') {
                        $male_count = $row['count'];
                    } else {
                        $female_count = $row['count'];
                    }
                }
                ?>
                <div class="gender-item">
                    <div class="gender-number"><?= $male_count ?></div>
                    <div>Male</div>
                </div>
                <div class="gender-item">
                    <div class="gender-number"><?= $female_count ?></div>
                    <div>Female</div>
                </div>
            </div>
        </div>

        <div class="report-section">
            <h2>Recent Registrations</h2>
            <?php if ($recent_registrations->rowCount() > 0): ?>
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($employee = $recent_registrations->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?= htmlspecialchars($employee['name']) ?></td>
                                <td><?= htmlspecialchars($employee['email']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $employee['status'] ?>">
                                        <?= ucfirst($employee['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($employee['created_at'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No employees registered yet.</p>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="agent_dashboard.php" class="btn">Back to Dashboard</a>
            <a href="manage_employees.php" class="btn">Manage Employees</a>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
    </footer>
</body>
</html> 