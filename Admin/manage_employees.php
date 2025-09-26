<?php
session_start();
require_once '../db_connect.php';

// Check admin session : jean luc 26 SEP 25
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$success = '';
$error = '';

// Handle status, verification updates, and deletion : jean luc 26 SEP 25
if (isset($_POST['action'], $_POST['employee_id'])) {
    $employee_id = $_POST['employee_id'];
    $action = $_POST['action'];

    if ($action === 'delete') {
        // Delete employee : jean luc 26 SEP 25
        $stmt = $conn->prepare('DELETE FROM employees WHERE ID = ?');
        if ($stmt->execute([$employee_id])) {
            $success = 'Employee deleted successfully.';
        } else {
            $error = 'Failed to delete employee.';
        }
    } elseif (in_array($action, ['activate', 'deactivate'])) {
        // Toggle active/inactive : jean luc 26 SEP 25
        $new_status = $action === 'activate' ? 'active' : 'inactive';
        $stmt = $conn->prepare('UPDATE employees SET Status = ? WHERE ID = ?');
        if ($stmt->execute([$new_status, $employee_id])) {
            $success = 'Employee status updated.';
        } else {
            $error = 'Failed to update status.';
        }
    } elseif ($action === 'toggle_verification') {
        // Toggle verification status : jean luc 26 SEP 25
        $stmt = $conn->prepare('SELECT Verification_status FROM employees WHERE ID = ?');
        $stmt->execute([$employee_id]);
        $current_status = $stmt->fetchColumn();

        // Cycle through unverified → pending → verified → unverified : jean luc 26 SEP 25
        if ($current_status === 'unverified') {
            $new_verification = 'pending';
        } elseif ($current_status === 'pending') {
            $new_verification = 'verified';
        } else {
            $new_verification = 'unverified';
        }

        $stmt = $conn->prepare('UPDATE employees SET Verification_status = ? WHERE ID = ?');
        if ($stmt->execute([$new_verification, $employee_id])) {
            $success = "Verification status changed to $new_verification.";
        } else {
            $error = 'Failed to update verification status.';
        }
    }
}

// Fetch employees with extended fields : jean luc 26 SEP 25
$stmt = $conn->query('
    SELECT 
        ID, Name, Gender, Age, Phone, Country, County_province,
        Skills, Experience, Education_level, Social_referee, Language,
        Email, National_id, Residence_type, salary_expectation,
        Verification_status, Created_at, Agent_id, Status, ID_passport
    FROM employees
    ORDER BY Created_at DESC
');
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta charset="UTF-8">
    <title>Manage Employees - Admin Dashboard</title>
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
            overflow-x: auto; /* Make table scrollable horizontally : jean luc 26 SEP 25 */
        }
        .content-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .employees-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            min-width: 1200px; /* Ensure table spans wide enough for scroll : jean luc 26 SEP 25 */
        }
        .employees-table th,
        .employees-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
            white-space: nowrap; /* Prevent breaking and force horizontal scroll : jean luc 26 SEP 25 */
        }
        .employees-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        .employees-table tr:hover {
            background: #f8f9fa;
        }
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85rem;
            margin-right: 5px;
        }
        .btn-delete { background: #e74c3c; color: white; }
        .btn-delete:hover { background: #c0392b; }
        .btn-toggle { background: #3498db; color: white; }
        .btn-toggle:hover { background: #2980b9; }
        .profile-thumb {
            width: 45px;
            height: 45px;
            border-radius: 4px;
            object-fit: cover;
        }
        .verification-unverified { color: red; font-weight: bold; }
        .verification-pending { color: orange; font-weight: bold; }
        .verification-verified { color: green; font-weight: bold; }
        .status-active { color: green; font-weight: bold; }
        .status-pending { color: orange; font-weight: bold; }
        .status-inactive { color: red; font-weight: bold; }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        /* Keep action buttons side by side : jean luc 26 SEP 25 */
        .action-buttons {
            display: flex;
            flex-wrap: nowrap;
            gap: 5px;
        }
        .action-buttons form {
            display: inline;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="admin-header">
        <h1>Manage Employees</h1>
        <p>View and manage registered employees</p>
    </div>

    <div class="admin-nav">
        <ul>
            <!-- Admin navigation : jean luc 26 SEP 25 -->
            <li><a href="https://homeworker.info/" style="color: #e74c3c;">Back</a></li>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="manage_agents.php">Manage Agents</a></li>
            <li><a href="manage_employees.php" class="active">Manage Employees</a></li>
            <li><a href="manage_employers.php">Manage Employers</a></li>
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
        <h3>Registered Employees (<?= count($employees) ?>)</h3>

        <?php if (count($employees) > 0): ?>
        <table class="employees-table">
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Phone</th>
                    <th>Gender</th><th>Age</th><th>Country</th><th>County</th>
                    <th>Skills</th><th>Education</th>
                    <th>Verification</th><th>Status</th>
                    <th>Agent</th><th>Salary</th><th>Registered</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $emp): ?>
                <tr>
                    <td><?= htmlspecialchars($emp['ID']) ?></td>
                    <td><strong><?= htmlspecialchars($emp['Name']) ?></strong></td>
                    <td><?= htmlspecialchars($emp['Email']) ?></td>
                    <td><?= htmlspecialchars($emp['Phone']) ?></td>
                    <td><?= htmlspecialchars($emp['Gender']) ?></td>
                    <td><?= htmlspecialchars($emp['Age']) ?></td>
                    <td><?= htmlspecialchars($emp['Country']) ?></td>
                    <td><?= htmlspecialchars($emp['County_province']) ?></td>
                    <td><?= htmlspecialchars($emp['Skills']) ?></td>
                    <td><?= htmlspecialchars($emp['Education_level']) ?></td>
                    <td class="verification-<?= htmlspecialchars($emp['Verification_status']) ?>"><?= htmlspecialchars($emp['Verification_status']) ?></td>
                    <td class="status-<?= htmlspecialchars($emp['Status']) ?>"><?= htmlspecialchars($emp['Status']) ?></td>
                    <td><?= htmlspecialchars($emp['Agent_id'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($emp['salary_expectation'] ?? 'N/A') ?></td>
                    <td><?= date('M j, Y', strtotime($emp['Created_at'])) ?></td>
                    <td>
                        <div class="action-buttons">
                            <!-- Toggle active/inactive -->
                            <form method="POST">
                                <input type="hidden" name="employee_id" value="<?= $emp['ID'] ?>">
                                <input type="hidden" name="action" value="<?= $emp['Status'] === 'active' ? 'deactivate' : 'activate' ?>">
                                <button type="submit" class="btn-action btn-toggle"><?= $emp['Status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
                            </form>

                            <!-- Toggle verification -->
                            <form method="POST">
                                <input type="hidden" name="employee_id" value="<?= $emp['ID'] ?>">
                                <input type="hidden" name="action" value="toggle_verification">
                                <button type="submit" class="btn-action btn-toggle">Next Verification</button>
                            </form>

                            <!-- Delete -->
                            <form method="POST" onsubmit="return confirm('Delete this employee?')">
                                <input type="hidden" name="employee_id" value="<?= $emp['ID'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn-action btn-delete">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="empty-state">
                <h4>No Employees Registered</h4>
                <p>Employees will appear here once they complete registration.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>