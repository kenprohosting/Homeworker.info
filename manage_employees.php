<?php
session_start();
require_once 'db_connect.php';

// Check if agent is logged in
if (!isset($_SESSION['agent_id'])) {
    header("Location: agent_login.php");
    exit();
}

$agent_id = $_SESSION['agent_id'];
$success = '';
$error = '';

// Handle status updates
if (isset($_POST['action']) && isset($_POST['employee_id'])) {
    $employee_id = $_POST['employee_id'];
    $action = $_POST['action'];
    
    // Verify the employee belongs to this agent
    $stmt = $conn->prepare("SELECT id FROM employees WHERE id = ? AND agent_id = ?");
    $stmt->execute([$employee_id, $agent_id]);
    $result = $stmt;
    
    if ($result->rowCount() > 0) {
        $new_status = '';
        switch ($action) {
            case 'activate':
                $new_status = 'active';
                break;
            case 'deactivate':
                $new_status = 'inactive';
                break;
            case 'delete':
                $stmt = $conn->prepare("DELETE FROM employees WHERE id = ? AND agent_id = ?");
                if ($stmt->execute([$employee_id, $agent_id])) {
                    $success = 'Employee deleted successfully';
                } else {
                    $error = 'Failed to delete employee';
                }
                break;
        }
        
        if ($new_status) {
            $stmt = $conn->prepare("UPDATE employees SET status = ? WHERE id = ? AND agent_id = ?");
            if ($stmt->execute([$new_status, $employee_id, $agent_id])) {
                $success = 'Employee status updated successfully';
            } else {
                $error = 'Failed to update employee status';
            }
        }
    } else {
        $error = 'Employee not found or unauthorized';
    }
}

// Get all employees for this agent
$stmt = $conn->prepare("SELECT id, name, email, phone, age, gender, skills, experience, country, county_province, status, created_at FROM employees WHERE agent_id = ? ORDER BY created_at DESC");
$stmt->execute([$agent_id]);
$employees = $stmt;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Employees - Homeworker Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <style>
        .employees-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .employee-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #197b88;
        }
        .employee-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .employee-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #197b88;
            margin: 0;
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
        .employee-details {
            margin-bottom: 15px;
        }
        .employee-details p {
            margin: 5px 0;
            color: #666;
            font-size: 0.9rem;
        }
        .employee-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-small {
            padding: 8px 15px;
            font-size: 0.9rem;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
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
                <li><a href="employee_register.php">Register Employee</a></li>
                <li><a href="agent_logout.php" class="logout-btn">Logout</a></li>
            </ul>
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>National ID</th><th>Status</th><th>Registered</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <h3>Filter Employees</h3>
            <form method="GET" class="filter-form">
                <select name="status">
                    <option value="">All Status</option>
                    <option value="active" <?= (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="pending" <?= (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="inactive" <?= (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
                <input type="text" name="search" placeholder="Search by name or email" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <button type="submit" class="btn">Filter</button>
                <a href="manage_employees.php" class="btn btn-secondary">Clear</a>
            </form>
        </div>

        <div class="employees-grid">
            <?php if ($employees->rowCount() > 0): ?>
                <?php while ($employee = $employees->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="employee-card">
                        <div class="employee-header">
                            <h3 class="employee-name"><?= htmlspecialchars($employee['name']) ?></h3>
                            <span class="status-badge status-<?= $employee['status'] ?>">
                                <?= ucfirst($employee['status']) ?>
                            </span>
                        </div>
                        
                        <div class="employee-details">
                            <p><strong>Email:</strong> <?= htmlspecialchars($employee['email']) ?></p>
                            <p><strong>Phone:</strong> <?= htmlspecialchars($employee['phone']) ?></p>
                            <p><strong>Age:</strong> <?= htmlspecialchars($employee['age']) ?> | <strong>Gender:</strong> <?= ucfirst($employee['gender']) ?></p>
                            <p><strong>Location:</strong> <?= htmlspecialchars($employee['country']) ?>, <?= htmlspecialchars($employee['county_province']) ?></p>
                            <p><strong>Skills:</strong> <?= htmlspecialchars($employee['skills']) ?></p>
                            <p><strong>Experience:</strong> <?= htmlspecialchars($employee['experience']) ?></p>
                            <p><strong>Registered:</strong> <?= date('M j, Y', strtotime($employee['created_at'])) ?></p>
                        </div>
                        
                        <!-- Removed employee-actions for view-only access -->
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <p>No employees found.</p>
                    <a href="employee_register.php" class="btn">Register Your First Employee</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date("Y") ?> KenPro. All rights reserved.</p>
    </footer>
</body>
</html> 