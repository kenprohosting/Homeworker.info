<?php
// Agent Manage Employees
// Agents may only EDIT their own employees (all fields). No delete, no separate status/verify actions.
// Check agent session : jean luc 26 SEP 25

session_start();
require_once 'db_connect.php';

// -----------------------------------------------------------------------------
// Session check and initial variables
// -----------------------------------------------------------------------------
if (!isset($_SESSION['agent_id'])) {
    header("Location: agent_login.php");
    exit();
}

$agent_id = (int) $_SESSION['agent_id'];
$success = '';
$error = '';

// -----------------------------------------------------------------------------
// AJAX: fetch a single employee (for modal pre-fill)
// Endpoint: manage_employees.php?ajax=get_employee&id=123
// Only returns the employee if it belongs to the logged-in agent.
// Check agent access before returning data.
// -----------------------------------------------------------------------------
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_employee' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Select columns and alias to lowercase keys so JS is predictable.
    $sql = "SELECT
                ID AS id,
                Name AS name,
                Email AS email,
                Phone AS phone,
                National_id AS national_id,
                Profile_pic AS profile_pic,
                ID_passport AS id_passport,
                Gender AS gender,
                Age AS age,
                Country AS country,
                County_province AS county_province,
                Skills AS skills,
                Experience AS experience,
                Education_level AS education_level,
                Social_referee AS social_referee,
                Health_conditions AS health_conditions,
                Language AS language,
                Residence_type AS residence_type,
                salary_expectation AS salary_expectation,
                Verification_status AS verification_status,
                Status AS status,
                Created_at AS created_at,
                Agent_id AS agent_id
            FROM employees
            WHERE ID = ? AND Agent_id = ? LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$id, $agent_id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($employee ?: []); // return empty object if not found (frontend will handle)
    exit();
}

// -----------------------------------------------------------------------------
// AJAX: update employee details via FormData (including optional files).
// Expects POST with 'ajax' === 'update_employee' and employee fields.
// Agent must own the employee. Files: profile_pic, id_passport allowed.
// Returns a short text message that includes the word 'success' on success.
// -----------------------------------------------------------------------------
if (isset($_POST['ajax']) && $_POST['ajax'] === 'update_employee') {
    // Very quick auth check
    if (!isset($_SESSION['agent_id'])) {
        echo 'Unauthorized';
        exit();
    }

    $employee_id = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
    if ($employee_id <= 0) {
        echo 'Invalid employee ID.';
        exit();
    }

    // Ensure this employee belongs to the agent
    $checkStmt = $conn->prepare("SELECT ID AS id, Profile_pic AS profile_pic, ID_passport AS id_passport FROM employees WHERE ID = ? AND Agent_id = ? LIMIT 1");
    $checkStmt->execute([$employee_id, $agent_id]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        // Either not existent or not owned by this agent
        echo 'Employee not found or unauthorized.';
        exit();
    }

    // Collect fields from POST (whitelist)
    // Use same field names as admin JS for consistency where applicable.
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
    // salary_expectation from split fields
    $salary_currency = trim($_POST['salary_currency'] ?? '');
    $salary_amount = trim($_POST['salary_amount'] ?? '');
    $salary_expectation = '';
    if ($salary_currency && $salary_amount) {
        $salary_expectation = $salary_currency . ' ' . $salary_amount;
    }

    // Basic validation (require name and valid email)
    if ($Name === '' || !filter_var($Email, FILTER_VALIDATE_EMAIL)) {
        echo 'Invalid input. Name and a valid Email are required.';
        exit();
    }

    // ---------------------------
    // File upload handling
    // ---------------------------
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        // attempt to create uploads dir (best effort)
        @mkdir($uploadDir, 0777, true);
    }

    // defaults: keep existing if no upload
    $profile_pic_db = $existing['profile_pic'] ?? null;
    $id_passport_db = $existing['id_passport'] ?? null;

    $allowedImageTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $allowedDocTypes = array_merge($allowedImageTypes, ['application/pdf']);
    $maxFileSize = 5 * 1024 * 1024; // 5 MB limit

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
            if (!move_uploaded_file($pp['tmp_name'], $dest)) {
                echo 'Failed to move uploaded profile picture.';
                exit();
            }
            // store relative path for DB to match other uploads
            $profile_pic_db = 'uploads/' . $safeName;
        } else {
            // ignore upload errors but report
            echo 'Error uploading profile picture.';
            exit();
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
            if (!move_uploaded_file($idf['tmp_name'], $dest)) {
                echo 'Failed to move uploaded ID/Passport.';
                exit();
            }
            $id_passport_db = 'uploads/' . $safeName;
        } else {
            echo 'Error uploading ID/Passport.';
            exit();
        }
    }

    // -------------------------------------------------------------------------
    // Build and execute UPDATE statement
    // Note: we intentionally update all editable fields here. Agent cannot change Agent_id.
    // We use the same column names that appear in registration/admin code (case-insensitive usually).
    // -------------------------------------------------------------------------
    try {
        $updateSql = "UPDATE employees SET
                        Name = :Name,
                        Email = :Email,
                        Phone = :Phone,
                        National_id = :National_id,
                        Gender = :Gender,
                        Age = :Age,
                        Country = :Country,
                        County_province = :County_province,
                        Skills = :Skills,
                        Experience = :Experience,
                        Education_level = :Education_level,
                        Social_referee = :Social_referee,
                        Language = :Language,
                        Residence_type = :Residence_type,
                        salary_expectation = :salary_expectation,
                        Profile_pic = :Profile_pic,
                        ID_passport = :ID_passport,
                        health_conditions = :Health_conditions
                      WHERE ID = :ID AND Agent_id = :Agent_id
                      LIMIT 1";

        $params = [
            ':Name' => $Name,
            ':Email' => $Email,
            ':Phone' => $Phone,
            ':National_id' => $National_id,
            ':Gender' => $Gender,
            ':Age' => $Age,
            ':Country' => $Country,
            ':County_province' => $County_province,
            ':Skills' => $Skills,
            ':Experience' => $Experience,
            ':Education_level' => $Education_level,
            ':Social_referee' => $Social_referee,
            ':Language' => $Language,
            ':Residence_type' => $Residence_type,
            ':salary_expectation' => $salary_expectation,
            ':Profile_pic' => $profile_pic_db,
            ':ID_passport' => $id_passport_db,
            ':Health_conditions' => $Health_conditions,
            ':ID' => $employee_id,
            ':Agent_id' => $agent_id
        ];

        $updStmt = $conn->prepare($updateSql);
        $ok = $updStmt->execute($params);

        if ($ok) {
            echo 'Employee updated successfully.';
        } else {
            echo 'Failed to update employee.';
        }
    } catch (Exception $ex) {
        // return a short safe message (don't leak DB internals)
        echo 'Failed to update employee.';
    }
    exit();
}

// -----------------------------------------------------------------------------
// Server-side: prepare employee list with optional filters (status + search).
// Keep the agent's nav & filtering UI; but the main display will be a table like admin's.
// -----------------------------------------------------------------------------
$where = " WHERE Agent_id = ? ";
$params = [$agent_id];

// status filter (GET)
if (isset($_GET['status']) && $_GET['status'] !== '') {
    $where .= " AND Status = ? ";
    $params[] = $_GET['status'];
}

// search filter (GET) - matches name, email or national id
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $q = '%' . trim($_GET['search']) . '%';
    $where .= " AND (Name LIKE ? OR Email LIKE ? OR National_id LIKE ?) ";
    $params[] = $q;
    $params[] = $q;
    $params[] = $q;
}

// Final select: alias columns to lowercase for consistent keys in PHP view
$sql = "SELECT
            ID AS id,
            Name AS name,
            Email AS email,
            Phone AS phone,
            National_id AS national_id,
            Profile_pic AS profile_pic,
            ID_passport AS id_passport,
            Gender AS gender,
            Age AS age,
            Country AS country,
            County_province AS county_province,
            Skills AS skills,
            Experience AS experience,
            Education_level AS education_level,
            Social_referee AS social_referee,
            Health_conditions AS health_conditions,
            Language AS language,
            Residence_type AS residence_type,
            salary_expectation AS salary_expectation,
            Verification_status AS verification_status,
            Status AS status,
            Created_at AS created_at,
            Agent_id AS agent_id
        FROM employees
        $where
        ORDER BY Created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// -----------------------------------------------------------------------------
// End PHP processing; render HTML (table + modal + scripts).
// -----------------------------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Employees - Agent Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- site stylesheet -->
    <link rel="stylesheet" href="styles.css">

    <!-- Flag icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons/css/flag-icons.min.css">

    <!-- Tagify CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">

    <style>
        /* ---------------------------------------------------------------------
           Agent manage_employees styles - mimic admin layout exactly
           --------------------------------------------------------------------- */

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

        /* Container like admin */
        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 16px 40px 16px;
        }

        /* Header */
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

        /* Nav (kept agent nav but styled like admin) */
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

        /* Filters area */
        .filter-section {
            background: var(--card-bg);
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 18px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }
        .filter-form { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
        .filter-form select, .filter-form input[type="text"] { padding:10px; border:1px solid #dfe7ea; border-radius:6px; min-width:180px; }
        .filter-form .btn { padding:10px 14px; background:linear-gradient(90deg,var(--accent-1),var(--accent-2)); color:white; border:none; border-radius:6px; cursor:pointer; }
        .filter-form .btn-secondary { background:#eef3f4; color:#333; border:1px solid #dfe7ea; }

        /* Content */
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

        /* Table */
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

        .action-buttons { display:flex; gap:8px; align-items:center; }

        .btn-action {
            padding: 8px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
        }
        .btn-edit { background:#2ecc71; color:#fff; }
        .btn-edit:hover { background:#27ae60; }

        /* status/verification badges */
        .status-active { background:#d4edda; color:#155724; padding:6px 10px; border-radius:12px; display:inline-block; font-weight:600; }
        .status-inactive { background:#f8d7da; color:#721c24; padding:6px 10px; border-radius:12px; display:inline-block; font-weight:600; }
        .status-pending { background:#fff3cd; color:#856404; padding:6px 10px; border-radius:12px; display:inline-block; font-weight:600; }
        .verification-verified { color:green; font-weight:700; }
        .verification-pending { color:orange; font-weight:700; }
        .verification-unverified { color:red; font-weight:700; }

        /* Modal (single modal reused) */
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

        /* Country dropdown */
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

        /* Salary flex */
        .salary-flex { display:flex; gap:8px; align-items:center; }
        .salary-flex select, .salary-flex input { flex:1; }

        @media (max-width: 900px) {
            .modal-grid { grid-template-columns: 1fr; }
            .employees-table { min-width: 900px; }
        }

    </style>
</head>
<body>
<div class="admin-container">

    <!-- header -->
    <div class="admin-header">
        <h1>Manage Employees</h1>
        <p>Agent portal — view and edit employees registered under your account</p>
    </div>

    <!-- nav (kept links similar to previous agent file) -->
    <div class="admin-nav" aria-label="agent navigation">
        <ul>
            <li><a href="agent_dashboard.php">Dashboard</a></li>
            <li><a href="employee_register.php">Register Employee</a></li>
            <li><a href="manage_employees.php" class="active">Manage Employees</a></li>
            <li><a href="agent_logout.php" style="color:#e74c3c;">Logout</a></li>
        </ul>
    </div>

    <!-- flash messages -->
    <?php if ($success): ?>
        <p class="success" style="background:#d4edda; color:#155724; padding:12px; border-radius:6px;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error" style="background:#f8d7da; color:#721c24; padding:12px; border-radius:6px;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <!-- Filters -->
    <div class="filter-section" role="region" aria-label="filters">
        <form method="GET" class="filter-form" style="align-items:center;">
            <label for="status">Status</label>
            <select name="status" id="status">
                <option value="">All Status</option>
                <option value="active" <?= (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                <option value="pending" <?= (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                <option value="inactive" <?= (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
            </select>

            <label for="search">Search</label>
            <input type="text" id="search" name="search" placeholder="Search by name, email or national ID" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">

            <button type="submit" class="btn">Filter</button>
            <a href="manage_employees.php" class="btn btn-secondary">Clear</a>
        </form>
    </div>

    <!-- content section: employee table -->
    <div class="content-section">
        <h3>Registered Employees (<?= count($employees) ?>)</h3>

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
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td>
                                <?php if (!empty($emp['profile_pic'])): ?>
                                    <img src="<?= htmlspecialchars($emp['profile_pic']) ?>" alt="Profile picture" class="profile-thumb" loading="lazy">
                                <?php else: ?>
                                    <img src="placeholder-user.png" alt="No photo" class="profile-thumb" loading="lazy">
                                <?php endif; ?>
                            </td>

                            <td><?= htmlspecialchars($emp['id']) ?></td>
                            <td><strong><?= htmlspecialchars($emp['name']) ?></strong></td>
                            <td><?= htmlspecialchars($emp['email']) ?></td>
                            <td><?= htmlspecialchars($emp['phone']) ?></td>
                            <td><?= htmlspecialchars($emp['national_id'] ?? '') ?></td>
                            <td><?= htmlspecialchars($emp['gender']) ?></td>
                            <td><?= htmlspecialchars($emp['age']) ?></td>
                            <td><?= htmlspecialchars($emp['country']) ?></td>
                            <td><?= htmlspecialchars($emp['county_province']) ?></td>
                            <td><?= htmlspecialchars($emp['skills']) ?></td>
                            <td><?= htmlspecialchars($emp['experience']) ?></td>
                            <td><?= htmlspecialchars($emp['education_level']) ?></td>
                            <td><?= htmlspecialchars($emp['social_referee']) ?></td>
                            <td><?= htmlspecialchars($emp['health_conditions'] ?? 'Not specified') ?></td>
                            <td><?= htmlspecialchars($emp['language']) ?></td>
                            <td><?= htmlspecialchars($emp['residence_type']) ?></td>
                            <td><?= htmlspecialchars($emp['salary_expectation']) ?></td>
                            <td class="verification-<?= htmlspecialchars(strtolower($emp['verification_status'] ?? 'unverified')) ?>"><?= htmlspecialchars($emp['verification_status'] ?? 'unverified') ?></td>
                            <td class="status-<?= htmlspecialchars(strtolower($emp['status'] ?? 'pending')) ?>"><?= htmlspecialchars($emp['status'] ?? 'pending') ?></td>
                            <td><?= date('M j, Y', strtotime($emp['created_at'])) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <!-- ONLY Edit permitted for agents. Modal pop-up handles all updates -->
                                    <button type="button" class="btn-action btn-edit" onclick="openEditModal(<?= (int)$emp['id'] ?>)">Edit</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state" style="padding:40px;text-align:center;">
                <h4>No Employees Found</h4>
                <p>There are no employees registered under your account yet.</p>
                <a href="employee_register.php" class="btn" style="margin-top:12px;">Register an Employee</a>
            </div>
        <?php endif; ?>

    </div> <!-- /.content-section -->

    <footer style="margin-top:18px; text-align:center; color:#666;">
        <p>&copy; <?= date("Y") ?> KenPro. All rights reserved.</p>
    </footer>

</div> <!-- /.admin-container -->

<!-- ---------------------------------------------------------------------
     Edit Modal (single modal reused for all rows; populated via AJAX)
     --------------------------------------------------------------------- -->
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
                                    <img id="currentPhoto" src="placeholder-user.png" alt="photo" style="width:120px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #e6eef0;">
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

                    <div style="margin-top:12px; display:flex; gap:12px; justify-content:flex-end;">
                        <button type="submit" class="btn-action btn-edit" style="min-width:110px;">Save Changes</button>
                        <button type="button" class="btn-action" onclick="closeEditModal()">Cancel</button>
                    </div>
                </div>

            </div> <!-- /.modal-grid -->
        </form>
    </div>
</div>

<!-- Tagify JS -->
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>

<!-- JavaScript: AJAX fetch for modal and AJAX submit to update -->
<script>
/**
 * Open edit modal and populate values via AJAX.
 * This mirrors admin behaviour but enforces agent ownership on server side.
 * openEditModal : jean luc 26 SEP 25
 */
function openEditModal(id) {
    var modal = document.getElementById('editModal');
    fetch('manage_employees.php?ajax=get_employee&id=' + encodeURIComponent(id), { credentials: 'same-origin' })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (!data || !data.id) {
                alert('Employee not found or you are not authorized to edit this employee.');
                return;
            }

            // Fill inputs - note server returns lowercase aliased keys
            document.getElementById('edit_id').value = data.id || '';
            document.getElementById('edit_national_id').value = data.national_id || '';
            document.getElementById('edit_name').value = data.name || '';
            document.getElementById('edit_email').value = data.email || '';
            document.getElementById('edit_phone').value = data.phone || '';
            document.getElementById('edit_gender').value = data.gender || '';
            document.getElementById('edit_age').value = (data.age !== null && data.age !== undefined) ? data.age : '';
            document.getElementById('edit_countryInput').value = data.country || '';
            document.getElementById('edit_county_province').value = data.county_province || '';
            document.getElementById('edit_residence_type').value = data.residence_type || '';
            document.getElementById('edit_skills').value = data.skills || '';
            document.getElementById('edit_experience').value = data.experience || '';
            document.getElementById('edit_education_level').value = data.education_level || '';
            document.getElementById('edit_social_referee').value = data.social_referee || '';
            document.getElementById('edit_health_conditions').value = data.health_conditions || '';
            document.getElementById('edit_language').value = data.language || '';

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
            if (data.skills && !standardSkills.includes(data.skills)) {
                skillsSelect.value = 'Other';
                skillsSpecify.value = data.skills;
                toggleSpecifyJobTitle('Other');
            } else {
                skillsSpecify.value = '';
                toggleSpecifyJobTitle(skillsSelect.value);
            }

            // Profile picture & ID link
            var currentPhoto = document.getElementById('currentPhoto');
            if (data.profile_pic) {
                currentPhoto.src = data.profile_pic;
            } else {
                currentPhoto.src = 'placeholder-user.png';
            }

            var currentIdLink = document.getElementById('currentIdLink');
            if (data.id_passport) {
                currentIdLink.href = data.id_passport;
                currentIdLink.textContent = 'View document';
            } else {
                currentIdLink.href = '#';
                currentIdLink.textContent = 'No document';
            }

            // Reset file inputs (clear any previous selection)
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
            if (data.language) {
                var langArray = data.language.split(',').map(l => l.trim()).filter(l => l);
                langArray.forEach(lang => {
                    window.editTagify.addTags(lang);
                });
            }

            // Show modal
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');

            // Scroll top so header is visible
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

/**
 * Close edit modal
 * closeEditModal : jean luc 26 SEP 25
 */
function closeEditModal() {
    var modal = document.getElementById('editModal');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    if (window.editTagify) {
        window.editTagify.destroy();
        window.editTagify = null;
    }
}

/**
 * Submit edit form via AJAX with FormData (supports file uploads).
 * Listens to 'submit' event of the form with id=editEmployeeForm
 * Similar to admin flow, returns a short text message; when that message contains
 * the word 'success' the frontend reloads to reflect changes.
 */
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
        // keep alert short and let server message guide reload
        alert(text);
        if (text && text.toLowerCase().indexOf('success') !== -1) {
            // reload to show updated table
            window.location.reload();
        }
    })
    .catch(function(err) {
        console.error(err);
        alert('Failed to update employee.');
    });
});

// Close modal when clicking outside of content
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

// Country dropdown script for edit modal
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

        // Hide dropdown when clicking outside
        document.addEventListener("click", function (e) {
            if (!editCountryList.contains(e.target) && e.target !== editCountryInput) {
                editCountryList.style.display = "none";
            }
        });
    }

    // Skills toggle init
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