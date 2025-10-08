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

// AJAX endpoint for fetching employee details : jean luc 26 SEP 25
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_employee' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $conn->prepare("SELECT 
                            ID, Name, Email, Phone, National_id, Profile_pic, ID_passport, 
                            Gender, Age, Country, County_province, Skills, Experience, 
                            Education_level, Social_referee, Health_conditions, Language, 
                            Residence_type, salary_expectation, Verification_status, 
                            Status, Created_at, Agent_id
                            FROM employees WHERE ID = ?");
    $stmt->execute([$id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($employee ?: []);
    exit;
}

// AJAX endpoint for updating employee details : jean luc 26 SEP 25
if (isset($_POST['ajax']) && $_POST['ajax'] === 'update_employee') {
    if (!isset($_SESSION['admin_id'])) {
        echo 'Unauthorized';
        exit;
    }

    $employee_id = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
    if ($employee_id <= 0) {
        echo 'Invalid employee ID.';
        exit();
    }

    $checkStmt = $conn->prepare("SELECT ID, Profile_pic, ID_passport FROM employees WHERE ID = ? LIMIT 1");
    $checkStmt->execute([$employee_id]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        echo 'Employee not found.';
        exit();
    }

    // Collect fields from POST
    $Name = trim($_POST['name'] ?? '');
    $Email = trim($_POST['email'] ?? '');
    $Phone = trim($_POST['phone'] ?? '');
    $National_id = trim($_POST['national_id'] ?? '');
    $Gender = trim($_POST['gender'] ?? '');
    $Age = isset($_POST['age']) && $_POST['age'] !== '' ? (int) $_POST['age'] : null;
    $Country = trim($_POST['country'] ?? '');
    $County_province = trim($_POST['county_province'] ?? '');
    $Skills = trim($_POST['skills'] ?? '');
    if ($Skills === 'Other') {
        $Skills = trim($_POST['skills_specify'] ?? '');
    }
    $Experience = trim($_POST['experience'] ?? '');
    $Education_level = trim($_POST['education_level'] ?? '');
    $Social_referee = trim($_POST['social_referee'] ?? '');
    $Health_conditions = trim($_POST['health_conditions'] ?? '');
    $Language = trim($_POST['language'] ?? '');
    $Residence_type = trim($_POST['residence_type'] ?? '');
    
    $salary_currency = trim($_POST['salary_currency'] ?? '');
    $salary_amount = trim($_POST['salary_amount'] ?? '');
    $salary_expectation = '';
    if ($salary_currency && $salary_amount) {
        $salary_expectation = $salary_currency . ' ' . $salary_amount;
    }

    if ($Name === '' || !filter_var($Email, FILTER_VALIDATE_EMAIL)) {
        echo 'Invalid input. Name and a valid Email are required.';
        exit();
    }

    // File upload handling
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }

    $profile_pic_db = $existing['Profile_pic'] ?? null;
    $id_passport_db = $existing['ID_passport'] ?? null;

    $allowedImageTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $allowedDocTypes = array_merge($allowedImageTypes, ['application/pdf']);
    $maxFileSize = 5 * 1024 * 1024;

    // profile_pic
    if (isset($_FILES['profile_pic']) && is_uploaded_file($_FILES['profile_pic']['tmp_name'])) {
        $pp = $_FILES['profile_pic'];
        if ($pp['error'] === UPLOAD_ERR_OK) {
            if ($pp['size'] > $maxFileSize) {
                echo 'Profile picture too large. Max 5MB allowed.';
                exit();
            }
            $mime = mime_content_type($pp['tmp_name']);
            if (!in_array($mime, $allowedImageTypes)) {
                echo 'Invalid profile picture type. Only JPG/PNG allowed.';
                exit();
            }
            $ext = pathinfo($pp['name'], PATHINFO_EXTENSION);
            $safeName = 'profile_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest = $uploadDir . $safeName;
            if (move_uploaded_file($pp['tmp_name'], $dest)) {
                $profile_pic_db = 'uploads/' . $safeName;
            } else {
                echo 'Failed to move uploaded profile picture.';
                exit();
            }
        }
    }

    // id_passport
    if (isset($_FILES['id_passport']) && is_uploaded_file($_FILES['id_passport']['tmp_name'])) {
        $idf = $_FILES['id_passport'];
        if ($idf['error'] === UPLOAD_ERR_OK) {
            if ($idf['size'] > $maxFileSize) {
                echo 'ID/Passport file too large. Max 5MB allowed.';
                exit();
            }
            $mime = mime_content_type($idf['tmp_name']);
            if (!in_array($mime, $allowedDocTypes)) {
                echo 'Invalid ID/Passport file type. Only JPG/PNG/PDF allowed.';
                exit();
            }
            $ext = pathinfo($idf['name'], PATHINFO_EXTENSION);
            $safeName = 'id_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest = $uploadDir . $safeName;
            if (move_uploaded_file($idf['tmp_name'], $dest)) {
                $id_passport_db = 'uploads/' . $safeName;
            } else {
                echo 'Failed to move uploaded ID/Passport.';
                exit();
            }
        }
    }

    // Update employee
    try {
        $updateSql = "UPDATE employees SET
                        Name = ?, Email = ?, Phone = ?, National_id = ?, Gender = ?, Age = ?,
                        Country = ?, County_province = ?, Skills = ?, Experience = ?, Education_level = ?,
                        Social_referee = ?, Language = ?, Residence_type = ?, salary_expectation = ?,
                        Profile_pic = ?, ID_passport = ?, health_conditions = ?
                      WHERE ID = ?";
        
        $params = [$Name, $Email, $Phone, $National_id, $Gender, $Age, $Country, $County_province,
                   $Skills, $Experience, $Education_level, $Social_referee, $Language, $Residence_type,
                   $salary_expectation, $profile_pic_db, $id_passport_db, $Health_conditions, $employee_id];

        $updStmt = $conn->prepare($updateSql);
        $ok = $updStmt->execute($params);

        if ($ok) {
            echo 'Employee updated successfully.';
        } else {
            echo 'Failed to update employee.';
        }
    } catch (Exception $ex) {
        echo 'Failed to update employee.';
    }
    exit();
}

// Handle status, verification updates, and deletion
if (isset($_POST['action'], $_POST['employee_id'])) {
    $employee_id = $_POST['employee_id'];
    $action = $_POST['action'];

    if ($action === 'delete') {
        $stmt = $conn->prepare('DELETE FROM employees WHERE ID = ?');
        if ($stmt->execute([$employee_id])) {
            $success = 'Employee deleted successfully.';
        } else {
            $error = 'Failed to delete employee.';
        }
    } elseif (in_array($action, ['activate', 'deactivate'])) {
        $new_status = $action === 'activate' ? 'active' : 'inactive';
        $stmt = $conn->prepare('UPDATE employees SET Status = ? WHERE ID = ?');
        if ($stmt->execute([$new_status, $employee_id])) {
            $success = 'Employee status updated.';
        } else {
            $error = 'Failed to update status.';
        }
    } elseif ($action === 'toggle_verification') {
        $stmt = $conn->prepare('SELECT Verification_status FROM employees WHERE ID = ?');
        $stmt->execute([$employee_id]);
        $current_status = $stmt->fetchColumn();

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

// Simple pagination
 $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
 $perPage = 10;
 $offset = ($page - 1) * $perPage;

// Get total count
 $totalEmployees = $conn->query('SELECT COUNT(*) FROM employees')->fetchColumn();
 $totalPages = ceil($totalEmployees / $perPage);

// Fetch employees with pagination
 $stmt = $conn->prepare("
    SELECT 
        ID, Name, Gender, Age, Phone, Country, County_province,
        Skills, Experience, Education_level, Social_referee, Language,
        Email, National_id, Residence_type, salary_expectation,
        Verification_status, Created_at, Agent_id, Status, ID_passport, Profile_pic, Health_conditions
    FROM employees
    ORDER BY Created_at DESC
    LIMIT $perPage OFFSET $offset
");
 $stmt->execute();
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
    
    <!-- Flag icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons/css/flag-icons.min.css">

    <!-- Tagify CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">

    <style>
        :root {
            --accent-1: #197b88;
            --accent-2: #1ec8c8;
            --muted: #666;
            --card-bg: #fff;
            --table-border: #eee;
            --admin-header-bg-start: #2c3e50;
            --admin-header-bg-end: #34495e;
            --blue: #3498db;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            background: #f4f7fa;
            color: #222;
        }

        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 16px 40px 16px;
        }

        .admin-header {
            background: linear-gradient(135deg, var(--admin-header-bg-start) 0%, var(--admin-header-bg-end) 100%);
            color: white;
            padding: 28px 20px;
            text-align: left;
            border-radius: 10px;
            margin-bottom: 18px;
        }

        .admin-header h1 { margin: 0 0 6px 0; font-size: 1.6rem; }
        .admin-header p { margin: 0; opacity: 0.9; }

        .admin-nav {
            background: var(--card-bg);
            padding: 12px 16px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            margin-bottom: 18px;
        }
        .admin-nav ul { list-style: none; margin: 0; padding: 0; display:flex; gap:12px; flex-wrap:wrap; align-items:center; }
        .admin-nav a { text-decoration: none; color: #2c3e50; padding: 8px 12px; border-radius:6px; }
        .admin-nav a.active { background: var(--blue); color:white; }

        .content-section {
            background: var(--card-bg);
            padding: 18px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            overflow-x: auto;
        }
        .content-section h3 {
            margin: 0 0 12px 0;
            color: #2c3e50;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--blue);
            display:inline-block;
        }

        .employees-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            min-width: 1300px;
        }
        .employees-table th, .employees-table td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid var(--table-border);
            vertical-align: middle;
            white-space: nowrap;
        }
        .employees-table th {
            background: #f8f9fa;
            font-weight: 700;
            color: #2c3e50;
            font-size: 0.95rem;
        }
        .employees-table tr:hover { background: #fbfdfe; }

        .profile-thumb {
            width: 56px;
            height: 56px;
            border-radius: 6px;
            object-fit: cover;
            display: inline-block;
            border: 1px solid #e6eef0;
        }

        .action-buttons { 
            display: flex; 
            flex-direction: row;
            gap: 5px; 
            align-items: flex-start;
        }

        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            width: 100%;
            text-align: left;
        }
        .btn-edit { background:#2ecc71; color:#fff; }
        .btn-edit:hover { background:#27ae60; }
        .btn-delete { background:#e74c3c; color:#fff; }
        .btn-delete:hover { background:#c0392b; }
        .btn-toggle { background:#3498db; color:#fff; }
        .btn-toggle:hover { background:#2980b9; }

        .action-buttons form {
            display: block;
            margin: 0;
            width: 100%;
        }

        .status-active { background:#d4edda; color:#155724; padding:6px 10px; border-radius:12px; display:inline-block; font-weight:600; }
        .status-inactive { background:#f8d7da; color:#721c24; padding:6px 10px; border-radius:12px; display:inline-block; font-weight:600; }
        .status-pending { background:#fff3cd; color:#856404; padding:6px 10px; border-radius:12px; display:inline-block; font-weight:600; }
        .verification-verified { color:green; font-weight:700; }
        .verification-pending { color:orange; font-weight:700; }
        .verification-unverified { color:red; font-weight:700; }

        .modal {
            display:none;
            position:fixed;
            z-index:9999;
            left:0; top:0;
            width:100%; height:100%;
            background: rgba(0,0,0,0.6);
            justify-content:center; align-items:center;
            padding: 24px;
        }
        .modal[aria-hidden="false"] { display:flex; }
        .modal-content {
            background:#fff;
            border-radius:10px;
            width: 900px;
            max-width: 98%;
            max-height: 90vh;
            overflow:auto;
            padding: 18px;
            position: relative;
        }
        .modal-close {
            position:absolute; right:12px; top:12px;
            width:36px; height:36px; border-radius:50%;
            background: var(--blue); color:#fff; text-align:center; line-height:36px;
            cursor:pointer; font-weight:700; font-size:18px;
        }
        .modal-grid {
            display:grid;
            grid-template-columns: 1fr 1fr;
            gap:12px;
        }
        .form-group { margin-bottom:10px; }
        .form-group label { display:block; font-weight:600; margin-bottom:6px; }
        .form-group input[type="text"], .form-group input[type="email"], .form-group input[type="number"], .form-group select, .form-group textarea {
            width:100%; padding:9px; border:1px solid #dfe7ea; border-radius:6px; box-sizing:border-box;
        }
        .form-group .small-note { font-size:0.85rem; color:var(--muted); margin-top:4px; }

        .country-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #dfe7ea;
            border-radius: 6px;
            max-height: 200px;
            overflow-y: auto;
            list-style: none;
            padding: 0;
            margin: 0;
            z-index: 10;
            display: none;
        }
        .country-dropdown li {
            padding: 8px 12px;
            cursor: pointer;
        }
        .country-dropdown li:hover {
            background: #f8f9fa;
        }

        .salary-flex { display:flex; gap:8px; align-items:center; }
        .salary-flex select, .salary-flex input { flex:1; }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        .pagination a, .pagination span {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            color: var(--blue);
        }
        .pagination a:hover {
            background: #f8f9fa;
        }
        .pagination .current {
            background: var(--blue);
            color: white;
        }
        .pagination .disabled {
            color: #ccc;
            pointer-events: none;
        }

        .modal-buttons {
            margin-top: 12px;
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .modal-buttons button {
            padding: 8px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            text-align: center;
            min-width: 120px;
        }
        .modal-buttons .btn-save {
            background: #2ecc71;
            color: white;
        }
        .modal-buttons .btn-save:hover {
            background: #27ae60;
        }
        .modal-buttons .btn-cancel {
            background: #95a5a6;
            color: white;
        }
        .modal-buttons .btn-cancel:hover {
            background: #7f8c8d;
        }

        @media (max-width: 900px) {
            .modal-grid { grid-template-columns: 1fr; }
            .employees-table { min-width: 900px; }
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
        <p class="success" style="background:#d4edda; color:#155724; padding:12px; border-radius:6px;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <p class="error" style="background:#f8d7da; color:#721c24; padding:12px; border-radius:6px;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="content-section">
        <h3>Registered Employees (<?= $totalEmployees ?>)</h3>

        <?php if (count($employees) > 0): ?>
        <table class="employees-table" role="table" aria-label="Employees table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>National ID</th>
                    <th>Gender</th>
                    <th>Age</th>
                    <th>Country</th>
                    <th>County</th>
                    <th>Skills</th>
                    <th>Experience</th>
                    <th>Education</th>
                    <th>Referee</th>
                    <th>Health Conditions</th>
                    <th>Language</th>
                    <th>Residence</th>
                    <th>Salary</th>
                    <th>Verification</th>
                    <th>Status</th>
                    <th>Agent</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $emp): ?>
                <tr>
                    <td>
                        <?php if (!empty($emp['Profile_pic'])): ?>
                            <img src="../<?= htmlspecialchars($emp['Profile_pic']) ?>" alt="Profile picture" class="profile-thumb" loading="lazy">
                        <?php else: ?>
                            <img src="../placeholder-user.png" alt="No photo" class="profile-thumb" loading="lazy">
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($emp['ID']) ?></td>
                    <td><strong><?= htmlspecialchars($emp['Name']) ?></strong></td>
                    <td><?= htmlspecialchars($emp['Email']) ?></td>
                    <td><?= htmlspecialchars($emp['Phone']) ?></td>
                    <td><?= htmlspecialchars($emp['National_id'] ?? '') ?></td>
                    <td><?= htmlspecialchars($emp['Gender']) ?></td>
                    <td><?= htmlspecialchars($emp['Age']) ?></td>
                    <td><?= htmlspecialchars($emp['Country']) ?></td>
                    <td><?= htmlspecialchars($emp['County_province']) ?></td>
                    <td><?= htmlspecialchars($emp['Skills']) ?></td>
                    <td><?= htmlspecialchars($emp['Experience']) ?></td>
                    <td><?= htmlspecialchars($emp['Education_level']) ?></td>
                    <td><?= htmlspecialchars($emp['Social_referee']) ?></td>
                    <td><?= htmlspecialchars($emp['Health_conditions'] ?? 'Not specified') ?></td>
                    <td><?= htmlspecialchars($emp['Language']) ?></td>
                    <td><?= htmlspecialchars($emp['Residence_type']) ?></td>
                    <td><?= htmlspecialchars($emp['salary_expectation']) ?></td>
                    <td class="verification-<?= htmlspecialchars(strtolower($emp['Verification_status'] ?? 'unverified')) ?>"><?= htmlspecialchars($emp['Verification_status'] ?? 'unverified') ?></td>
                    <td class="status-<?= htmlspecialchars(strtolower($emp['Status'] ?? 'pending')) ?>"><?= htmlspecialchars($emp['Status'] ?? 'pending') ?></td>
                    <td><?= htmlspecialchars($emp['Agent_id'] ?? 'N/A') ?></td>
                    <td><?= date('M j, Y', strtotime($emp['Created_at'])) ?></td>
                    <td>
                        <div class="action-buttons">
                            <form method="POST">
                                <input type="hidden" name="employee_id" value="<?= $emp['ID'] ?>">
                                <input type="hidden" name="action" value="<?= $emp['Status'] === 'active' ? 'deactivate' : 'activate' ?>">
                                <button type="submit" class="btn-action btn-toggle"><?= $emp['Status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
                            </form>

                            <form method="POST">
                                <input type="hidden" name="employee_id" value="<?= $emp['ID'] ?>">
                                <input type="hidden" name="action" value="toggle_verification">
                                <button type="submit" class="btn-action btn-toggle">Change Verification Status</button>
                            </form>

                            <button type="button" class="btn-action btn-edit" onclick="openEditModal(<?= (int)$emp['ID'] ?>)">Edit</button>

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
        
        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>">« Previous</a>
            <?php else: ?>
                <span class="disabled">« Previous</span>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 3); $i <= min($totalPages, $page + 3); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>">Next »</a>
            <?php else: ?>
                <span class="disabled">Next »</span>
            <?php endif; ?>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <h4>No Employees Registered</h4>
                <p>Employees will appear here once they complete registration.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal" aria-hidden="true" role="dialog" aria-labelledby="editModalTitle">
    <div class="modal-content" role="document">
        <div class="modal-close" onclick="closeEditModal()" title="Close">&times;</div>
        <h3 id="editModalTitle">Edit Employee</h3>

        <form id="editEmployeeForm" enctype="multipart/form-data" autocomplete="off" novalidate>
            <input type="hidden" name="employee_id" id="edit_id">

            <div class="modal-grid">

                <div>
                    <div class="form-group">
                        <label for="edit_national_id">National ID/Passport Number</label>
                        <input type="text" name="national_id" id="edit_national_id" value="">
                    </div>
                    <div class="form-group">
                        <label for="edit_name">Full Name</label>
                        <input type="text" name="name" id="edit_name" required value="">
                    </div>
                    <div class="form-group">
                        <label for="edit_gender">Gender</label>
                        <select name="gender" id="edit_gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_age">Age (18+)</label>
                        <input type="number" name="age" id="edit_age" min="18" max="99" required value="">
                    </div>
                    <div class="form-group">
                        <label for="edit_phone">Phone</label>
                        <input type="text" name="phone" id="edit_phone" required value="">
                    </div>
                    <div class="form-group">
                        <label for="edit_countryInput">Country</label>
                        <div style="position:relative;">
                          <input type="text" id="edit_countryInput" name="country" placeholder="Country" autocomplete="off" required value="">
                          <ul id="edit_countryList" class="country-dropdown"></ul>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_county_province">County/Province</label>
                        <input type="text" name="county_province" id="edit_county_province" required value="">
                    </div>
                    <div class="form-group">
                        <label for="edit_residence_type">Residence Type</label>
                        <select name="residence_type" id="edit_residence_type" required>
                            <option value="">Residence Type</option>
                            <option value="urban">Urban</option>
                            <option value="rural">Rural</option>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label for="edit_skills">Job Title</label>
                        <select name="skills" id="edit_skills" required onchange="toggleSpecifyJobTitle(this.value)">
                            <option value="">Select Job Title</option>
                            <option value="Housegirl">Housegirl</option>
                            <option value="Houseboy">Houseboy</option>
                            <option value="Shambaboy">Shambaboy</option>
                            <option value="Gatekeeper">Gatekeeper</option>
                            <option value="Cook">Cook</option>
                            <option value="Gardener">Gardener</option>
                            <option value="Driver">Driver</option>
                            <option value="Nanny">Nanny</option>
                            <option value="Cleaner">Cleaner</option>
                            <option value="Other">Other (Specify)</option>
                        </select>
                        <input type="text" name="skills_specify" id="edit_skills_specify" placeholder="Please specify job title" style="display:none;margin-top:8px;" value="" oninput="if(this.value){document.getElementById('edit_skills').value='Other';toggleSpecifyJobTitle('Other');}">
                    </div>
                    <div class="form-group">
                        <label for="edit_salary_currency">Salary Expectation</label>
                        <div class="salary-flex">
                            <select name="salary_currency" id="edit_salary_currency" required>
                                <option value="KES">🇰🇪 KES</option>
                                <option value="USD">🇺🇸 USD</option>
                                <option value="EUR">🇪🇺 EUR</option>
                                <option value="GBP">🇬🇧 GBP</option>
                                <option value="UGX">🇺🇬 UGX</option>
                                <option value="TZS">🇹🇿 TZS</option>
                                <option value="RWF">🇷🇼 RWF</option>
                            </select>
                            <input type="number" name="salary_amount" id="edit_salary_amount" placeholder="Amount" min="0" required value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_experience">Experience</label>
                        <input type="text" name="experience" id="edit_experience" placeholder="e.g. 5 years" value="">
                    </div>
                    <div class="form-group">
                        <label for="edit_education_level">Education Level</label>
                        <select name="education_level" id="edit_education_level" required>
                            <option value="">Select Education Level</option>
                            <option value="Primary">Primary</option>
                            <option value="Secondary">Secondary</option>
                            <option value="Tertiary">Tertiary</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_social_referee">Social Referee Contact</label>
                        <input type="text" name="social_referee" id="edit_social_referee" placeholder="Social Referee Contact" value="">
                    </div>
                    <div class="form-group">
                        <label for="edit_language">Languages</label>
                        <input type="text" id="edit_language" name="language" placeholder="Type and select languages" value="">
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email Address</label>
                        <input type="email" name="email" id="edit_email" required placeholder="e.g. janedoe@email.com" value="">
                    </div>
                </div>

                <!-- full width controls for file uploads and notes -->
                <div style="grid-column:1 / -1;">
                    <div style="display:flex; gap:12px; align-items:flex-start;">
                        <div style="flex:0 0 160px;">
                            <div class="form-group">
                                <label>Current Photo</label>
                                <div id="currentPhotoWrap" style="margin-bottom:8px;">
                                    <img id="currentPhoto" src="../placeholder-user.png" alt="photo" style="width:120px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #e6eef0;">
                                </div>
                                <div class="form-group">
                                    <label for="edit_profile_pic">Upload new profile picture</label>
                                    <input type="file" id="edit_profile_pic" name="profile_pic" accept="image/*">
                                    <div class="small-note">JPG/PNG. Leave empty to keep current picture.</div>
                                </div>
                            </div>
                        </div>

                        <div style="flex:1;">
                            <div class="form-group">
                                <label>Current ID/Passport</label>
                                <div id="currentIdWrap" style="margin-bottom:8px;">
                                    <a id="currentIdLink" href="#" target="_blank" rel="noopener">No document</a>
                                </div>

                                <div class="form-group">
                                    <label for="edit_id_passport">Upload new ID/Passport</label>
                                    <input type="file" id="edit_id_passport" name="id_passport" accept="image/*,.pdf">
                                    <div class="small-note">JPG/PNG/PDF. Leave empty to keep current document.</div>
                                </div>
                            </div>

                            <div style="margin-top:10px;">
                                <div class="form-group">
                                    <label for="edit_health_conditions">Health Conditions</label>
                                    <textarea id="edit_health_conditions" name="health_conditions" rows="3" placeholder="e.g. None, Asthma, Allergy to penicillin..."></textarea>
                                    <div class="small-note">Briefly describe any relevant health conditions.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-buttons">
                        <button type="submit" class="btn-save">Save Changes</button>
                        <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                    </div>
                </div>

            </div> <!-- /.modal-grid -->
        </form>
    </div>
</div>

<!-- Tagify JS -->
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>

<!-- JavaScript -->
<script>
function openEditModal(id) {
    var modal = document.getElementById('editModal');
    fetch('manage_employees.php?ajax=get_employee&id=' + encodeURIComponent(id), { credentials: 'same-origin' })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (!data || !data.ID) {
                alert('Employee not found.');
                return;
            }

            // Fill inputs
            document.getElementById('edit_id').value = data.ID || '';
            document.getElementById('edit_national_id').value = data.National_id || '';
            document.getElementById('edit_name').value = data.Name || '';
            document.getElementById('edit_email').value = data.Email || '';
            document.getElementById('edit_phone').value = data.Phone || '';
            document.getElementById('edit_gender').value = data.Gender || '';
            document.getElementById('edit_age').value = (data.Age !== null && data.Age !== undefined) ? data.Age : '';
            document.getElementById('edit_countryInput').value = data.Country || '';
            document.getElementById('edit_county_province').value = data.County_province || '';
            document.getElementById('edit_residence_type').value = data.Residence_type || '';
            document.getElementById('edit_skills').value = data.Skills || '';
            document.getElementById('edit_experience').value = data.Experience || '';
            document.getElementById('edit_education_level').value = data.Education_level || '';
            document.getElementById('edit_social_referee').value = data.Social_referee || '';
            document.getElementById('edit_health_conditions').value = data.Health_conditions || '';
            document.getElementById('edit_language').value = data.Language || '';

            // Salary parse
            var salaryMatch = data.salary_expectation ? data.salary_expectation.match(/([A-Z]{3})\s*(\d+)/i) : null;
            if (salaryMatch) {
                document.getElementById('edit_salary_currency').value = salaryMatch[1];
                document.getElementById('edit_salary_amount').value = salaryMatch[2];
            }

            // Skills: if not standard, set to Other and specify
            var skillsSelect = document.getElementById('edit_skills');
            var skillsSpecify = document.getElementById('edit_skills_specify');
            var standardSkills = ['Housegirl', 'Houseboy', 'Shambaboy', 'Gatekeeper', 'Cook', 'Gardener', 'Driver', 'Nanny', 'Cleaner'];
            if (data.Skills && !standardSkills.includes(data.Skills)) {
                skillsSelect.value = 'Other';
                skillsSpecify.value = data.Skills;
                toggleSpecifyJobTitle('Other');
            } else {
                skillsSpecify.value = '';
                toggleSpecifyJobTitle(skillsSelect.value);
            }

            // Profile picture & ID link
            var currentPhoto = document.getElementById('currentPhoto');
            if (data.Profile_pic) {
                currentPhoto.src = '../' + data.Profile_pic;
            } else {
                currentPhoto.src = '../placeholder-user.png';
            }

            var currentIdLink = document.getElementById('currentIdLink');
            if (data.ID_passport) {
                currentIdLink.href = '../' + data.ID_passport;
                currentIdLink.textContent = 'View document';
            } else {
                currentIdLink.href = '#';
                currentIdLink.textContent = 'No document';
            }

            // Reset file inputs
            document.getElementById('edit_profile_pic').value = '';
            document.getElementById('edit_id_passport').value = '';

            // Tagify for languages
            var languageInput = document.getElementById('edit_language');
            if (window.editTagify) {
                window.editTagify.destroy();
            }
            window.editTagify = new Tagify(languageInput, {
                whitelist: ['English', 'Swahili', 'French', 'German', 'Spanish', 'Arabic', 'Somali', 'Luo', 'Kikuyu', 'Luhya', 'Kalenjin', 'Kamba', 'Kisii', 'Meru', 'Mijikenda', 'Maasai', 'Turkana', 'Mandarin Chinese', 'Hindi', 'Portuguese', 'Russian', 'Italian', 'Japanese', 'Korean', 'Bengali', 'Urdu', 'Indonesian'],
                maxTags: 10,
                dropdown: { maxItems: 20, enabled: 0 }
            });

            // Load existing languages as tags
            if (data.Language) {
                var langArray = data.Language.split(',').map(l => l.trim()).filter(l => l);
                langArray.forEach(lang => {
                    window.editTagify.addTags(lang);
                });
            }

            // Show modal
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            modal.querySelector('.modal-content').scrollTop = 0;
        })
        .catch(function(err) {
            console.error(err);
            alert('Failed to fetch employee details.');
        });
}

function toggleSpecifyJobTitle(val) {
    var specify = document.getElementById('edit_skills_specify');
    if (val === 'Other') {
        specify.style.display = 'block';
        specify.disabled = false;
        specify.required = true;
        specify.focus();
    } else {
        specify.style.display = 'none';
        specify.disabled = true;
        specify.required = false;
        specify.value = '';
    }
}

function closeEditModal() {
    var modal = document.getElementById('editModal');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    if (window.editTagify) {
        window.editTagify.destroy();
        window.editTagify = null;
    }
}

document.getElementById('editEmployeeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Set Tagify value to comma-separated string for submission
    var languageInput = document.getElementById('edit_language');
    if (window.editTagify) {
        languageInput.value = window.editTagify.value.map(function(tag) { return tag.value; }).join(', ');
    }

    var form = document.getElementById('editEmployeeForm');
    var fd = new FormData(form);
    fd.append('ajax', 'update_employee');

    fetch('manage_employees.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
    })
    .then(function(res) { return res.text(); })
    .then(function(text) {
        alert(text);
        if (text && text.toLowerCase().indexOf('success') !== -1) {
            window.location.reload();
        }
    })
    .catch(function(err) {
        console.error(err);
        alert('Failed to update employee.');
    });
});

// Close modal when clicking outside content
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

// Close modal on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var modal = document.getElementById('editModal');
        if (modal && modal.style.display === 'flex') closeEditModal();
    }
});

// Country dropdown script
document.addEventListener('DOMContentLoaded', function() {
    const editCountryInput = document.getElementById("edit_countryInput");
    const editCountryList = document.getElementById("edit_countryList");
    const countries = [
        { name: "Afghanistan", flag: "🇦🇫" },
        { name: "Albania", flag: "🇦🇱" },
        { name: "Algeria", flag: "🇩🇿" },
        { name: "Andorra", flag: "🇦🇩" },
        { name: "Angola", flag: "🇦🇴" },
        { name: "Antigua and Barbuda", flag: "🇦🇬" },
        { name: "Argentina", flag: "🇦🇷" },
        { name: "Armenia", flag: "🇦🇲" },
        { name: "Australia", flag: "🇦🇺" },
        { name: "Austria", flag: "🇦🇹" },
        { name: "Azerbaijan", flag: "🇦🇿" },
        { name: "Bahamas", flag: "🇧🇸" },
        { name: "Bahrain", flag: "🇧🇭" },
        { name: "Bangladesh", flag: "🇧🇩" },
        { name: "Barbados", flag: "🇧🇧" },
        { name: "Belarus", flag: "🇧🇾" },
        { name: "Belgium", flag: "🇧🇪" },
        { name: "Belize", flag: "🇧🇿" },
        { name: "Benin", flag: "🇧🇯" },
        { name: "Bhutan", flag: "🇧🇹" },
        { name: "Bolivia", flag: "🇧🇴" },
        { name: "Bosnia and Herzegovina", flag: "🇧🇦" },
        { name: "Botswana", flag: "🇧🇼" },
        { name: "Brazil", flag: "🇧🇷" },
        { name: "Brunei", flag: "🇧🇳" },
        { name: "Bulgaria", flag: "🇧🇬" },
        { name: "Burkina Faso", flag: "🇧🇫" },
        { name: "Burundi", flag: "🇧🇮" },
        { name: "Cabo Verde", flag: "🇨🇻" },
        { name: "Cambodia", flag: "🇰🇭" },
        { name: "Cameroon", flag: "🇨🇲" },
        { name: "Canada", flag: "🇨🇦" },
        { name: "Central African Republic", flag: "🇨🇫" },
        { name: "Chad", flag: "🇹🇩" },
        { name: "Chile", flag: "🇨🇱" },
        { name: "China", flag: "🇨🇳" },
        { name: "Colombia", flag: "🇨🇴" },
        { name: "Comoros", flag: "🇰🇲" },
        { name: "Congo (Brazzaville)", flag: "🇨🇬" },
        { name: "Congo (Kinshasa)", flag: "🇨🇩" },
        { name: "Costa Rica", flag: "🇨🇷" },
        { name: "Croatia", flag: "🇭🇷" },
        { name: "Cuba", flag: "🇨🇺" },
        { name: "Cyprus", flag: "🇨🇾" },
        { name: "Czech Republic", flag: "🇨🇿" },
        { name: "Denmark", flag: "🇩🇰" },
        { name: "Djibouti", flag: "🇩🇯" },
        { name: "Dominica", flag: "🇩🇲" },
        { name: "Dominican Republic", flag: "🇩🇴" },
        { name: "Ecuador", flag: "🇪🇨" },
        { name: "Egypt", flag: "🇪🇬" },
        { name: "El Salvador", flag: "🇸🇻" },
        { name: "Equatorial Guinea", flag: "🇬🇶" },
        { name: "Eritrea", flag: "🇪🇷" },
        { name: "Estonia", flag: "🇪🇪" },
        { name: "Eswatini", flag: "🇸🇿" },
        { name: "Ethiopia", flag: "🇪🇹" },
        { name: "Fiji", flag: "🇫🇯" },
        { name: "Finland", flag: "🇫🇮" },
        { name: "France", flag: "🇫🇷" },
        { name: "Gabon", flag: "🇬🇦" },
        { name: "Gambia", flag: "🇬🇲" },
        { name: "Georgia", flag: "🇬🇪" },
        { name: "Germany", flag: "🇩🇪" },
        { name: "Ghana", flag: "🇬🇭" },
        { name: "Greece", flag: "🇬🇷" },
        { name: "Grenada", flag: "🇬🇩" },
        { name: "Guatemala", flag: "🇬🇹" },
        { name: "Guinea", flag: "🇬🇳" },
        { name: "Guinea-Bissau", flag: "🇬🇼" },
        { name: "Guyana", flag: "🇬🇾" },
        { name: "Haiti", flag: "🇭🇹" },
        { name: "Honduras", flag: "🇭🇳" },
        { name: "Hungary", flag: "🇭🇺" },
        { name: "Iceland", flag: "🇮🇸" },
        { name: "India", flag: "🇮🇳" },
        { name: "Indonesia", flag: "🇮🇩" },
        { name: "Iran", flag: "🇮🇷" },
        { name: "Iraq", flag: "🇮🇶" },
        { name: "Ireland", flag: "🇮🇪" },
        { name: "Israel", flag: "🇮🇱" },
        { name: "Italy", flag: "🇮🇹" },
        { name: "Jamaica", flag: "🇯🇲" },
        { name: "Japan", flag: "🇯🇵" },
        { name: "Jordan", flag: "🇯🇴" },
        { name: "Kazakhstan", flag: "🇰🇿" },
        { name: "Kenya", flag: "🇰🇪" },
        { name: "Kiribati", flag: "🇰🇮" },
        { name: "Kuwait", flag: "🇰🇼" },
        { name: "Kyrgyzstan", flag: "🇰🇬" },
        { name: "Laos", flag: "🇱🇦" },
        { name: "Latvia", flag: "🇱🇻" },
        { name: "Lebanon", flag: "🇱🇧" },
        { name: "Lesotho", flag: "🇱🇸" },
        { name: "Liberia", flag: "🇱🇷" },
        { name: "Libya", flag: "🇱🇾" },
        { name: "Liechtenstein", flag: "🇱🇮" },
        { name: "Lithuania", flag: "🇱🇹" },
        { name: "Luxembourg", flag: "🇱🇺" },
        { name: "Madagascar", flag: "🇲🇬" },
        { name: "Malawi", flag: "🇲🇼" },
        { name: "Malaysia", flag: "🇲🇾" },
        { name: "Maldives", flag: "🇲🇻" },
        { name: "Mali", flag: "🇲🇱" },
        { name: "Malta", flag: "🇲🇹" },
        { name: "Marshall Islands", flag: "🇲🇭" },
        { name: "Mauritania", flag: "🇲🇷" },
        { name: "Mauritius", flag: "🇲🇺" },
        { name: "Mexico", flag: "🇲🇽" },
        { name: "Micronesia", flag: "🇫🇲" },
        { name: "Moldova", flag: "🇲🇩" },
        { name: "Monaco", flag: "🇲🇨" },
        { name: "Mongolia", flag: "🇲🇳" },
        { name: "Montenegro", flag: "🇲🇪" },
        { name: "Morocco", flag: "🇲🇦" },
        { name: "Mozambique", flag: "🇲🇿" },
        { name: "Myanmar", flag: "🇲🇲" },
        { name: "Namibia", flag: "🇳🇦" },
        { name: "Nauru", flag: "🇳🇷" },
        { name: "Nepal", flag: "🇳🇵" },
        { name: "Netherlands", flag: "🇳🇱" },
        { name: "New Zealand", flag: "🇳🇿" },
        { name: "Nicaragua", flag: "🇳🇮" },
        { name: "Niger", flag: "🇳🇪" },
        { name: "Nigeria", flag: "🇳🇬" },
        { name: "North Korea", flag: "🇰🇵" },
        { name: "North Macedonia", flag: "🇲🇰" },
        { name: "Norway", flag: "🇳🇴" },
        { name: "Oman", flag: "🇴🇲" },
        { name: "Pakistan", flag: "🇵🇰" },
        { name: "Palau", flag: "🇵🇼" },
        { name: "Panama", flag: "🇵🇦" },
        { name: "Papua New Guinea", flag: "🇵🇬" },
        { name: "Paraguay", flag: "🇵🇾" },
        { name: "Peru", flag: "🇵🇪" },
        { name: "Philippines", flag: "🇵🇭" },
        { name: "Poland", flag: "🇵🇱" },
        { name: "Portugal", flag: "🇵🇹" },
        { name: "Qatar", flag: "🇶🇦" },
        { name: "Romania", flag: "🇷🇴" },
        { name: "Russia", flag: "🇷🇺" },
        { name: "Rwanda", flag: "🇷🇼" },
        { name: "Saint Kitts and Nevis", flag: "🇰🇳" },
        { name: "Saint Lucia", flag: "🇱🇨" },
        { name: "Saint Vincent and the Grenadines", flag: "🇻🇨" },
        { name: "Samoa", flag: "🇼🇸" },
        { name: "San Marino", flag: "🇸🇲" },
        { name: "Sao Tome and Principe", flag: "🇸🇹" },
        { name: "Saudi Arabia", flag: "🇸🇦" },
        { name: "Senegal", flag: "🇸🇳" },
        { name: "Serbia", flag: "🇷🇸" },
        { name: "Seychelles", flag: "🇸🇨" },
        { name: "Sierra Leone", flag: "🇸🇱" },
        { name: "Singapore", flag: "🇸🇬" },
        { name: "Slovakia", flag: "🇸🇰" },
        { name: "Slovenia", flag: "🇸🇮" },
        { name: "Solomon Islands", flag: "🇸🇧" },
        { name: "Somalia", flag: "🇸🇴" },
        { name: "South Africa", flag: "🇿🇦" },
        { name: "South Korea", flag: "🇰🇷" },
        { name: "South Sudan", flag: "🇸🇸" },
        { name: "Spain", flag: "🇪🇸" },
        { name: "Sri Lanka", flag: "🇱🇰" },
        { name: "Sudan", flag: "🇸🇩" },
        { name: "Suriname", flag: "🇸🇷" },
        { name: "Sweden", flag: "🇸🇪" },
        { name: "Switzerland", flag: "🇨🇭" },
        { name: "Syria", flag: "🇸🇾" },
        { name: "Taiwan", flag: "🇹🇼" },
        { name: "Tajikistan", flag: "🇹🇯" },
        { name: "Tanzania", flag: "🇹🇿" },
        { name: "Thailand", flag: "🇹🇭" },
        { name: "Timor-Leste", flag: "🇹🇱" },
        { name: "Togo", flag: "🇹🇬" },
        { name: "Tonga", flag: "🇹🇴" },
        { name: "Trinidad and Tobago", flag: "🇹🇹" },
        { name: "Tunisia", flag: "🇹🇳" },
        { name: "Turkey", flag: "🇹🇷" },
        { name: "Turkmenistan", flag: "🇹🇲" },
        { name: "Tuvalu", flag: "🇹🇻" },
        { name: "Uganda", flag: "🇺🇬" },
        { name: "Ukraine", flag: "🇺🇦" },
        { name: "United Arab Emirates", flag: "🇦🇪" },
        { name: "United Kingdom", flag: "🇬🇧" },
        { name: "United States", flag: "🇺🇸" },
        { name: "Uruguay", flag: "🇺🇾" },
        { name: "Uzbekistan", flag: "🇺🇿" },
        { name: "Vanuatu", flag: "🇻🇺" },
        { name: "Vatican City", flag: "🇻🇦" },
        { name: "Venezuela", flag: "🇻🇪" },
        { name: "Vietnam", flag: "🇻🇳" },
        { name: "Yemen", flag: "🇾🇪" },
        { name: "Zambia", flag: "🇿🇲" },
        { name: "Zimbabwe", flag: "🇿🇼" }
    ];

    if (editCountryInput && editCountryList) {
        editCountryInput.addEventListener("input", function () {
            const input = this.value.toLowerCase();
            editCountryList.innerHTML = "";

            if (input.length === 0) {
                editCountryList.style.display = "none";
                return;
            }

            const filtered = countries.filter(c =>
                c.name.toLowerCase().startsWith(input)
            );

            filtered.forEach(c => {
                const li = document.createElement("li");
                li.textContent = `${c.flag} ${c.name}`;
                li.addEventListener("click", () => {
                    editCountryInput.value = c.name;
                    editCountryList.innerHTML = "";
                    editCountryList.style.display = "none";
                });
                editCountryList.appendChild(li);
            });

            editCountryList.style.display = filtered.length ? "block" : "none";
        });

        document.addEventListener("click", function (e) {
            if (!editCountryList.contains(e.target) && e.target !== editCountryInput) {
                editCountryList.style.display = "none";
            }
        });
    }

    var editSkills = document.getElementById('edit_skills');
    if (editSkills) {
        editSkills.addEventListener('change', function() {
            toggleSpecifyJobTitle(this.value);
        });
    }
});
</script>
</body>
</html>