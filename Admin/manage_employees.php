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
    $stmt = $conn->prepare("SELECT ID, Name, Email, Phone, Gender, Age, Country, County_province, Skills, Education_level, salary_expectation
                            FROM employees WHERE ID = ?");
    $stmt->execute([$id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($employee ?: []);
    exit;
}

// AJAX endpoint for updating employee details : jean luc 26 SEP 25
if (isset($_POST['ajax']) && $_POST['ajax'] === 'update_employee') {
    // Ensure admin session still valid
    if (!isset($_SESSION['admin_id'])) {
        echo 'Unauthorized';
        exit;
    }

    $id = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
    // Whitelist fields (Agent_id intentionally excluded)
    $name = trim($_POST['Name'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $phone = trim($_POST['Phone'] ?? '');
    $gender = trim($_POST['Gender'] ?? '');
    $age = (isset($_POST['Age']) && $_POST['Age'] !== '') ? (int) $_POST['Age'] : null;
    $country = trim($_POST['Country'] ?? '');
    $county = trim($_POST['County_province'] ?? '');
    $skills = trim($_POST['Skills'] ?? '');
    $education = trim($_POST['Education_level'] ?? '');
    $salary = trim($_POST['salary_expectation'] ?? '');

    // Basic validation
    if ($id <= 0 || $name === '' || $email === '') {
        echo 'Invalid input.';
        exit;
    }

    $stmt = $conn->prepare("UPDATE employees 
                            SET Name = ?, Email = ?, Phone = ?, Gender = ?, Age = ?, Country = ?, County_province = ?, Skills = ?, Education_level = ?, salary_expectation = ?
                            WHERE ID = ?");
    $params = [$name, $email, $phone, $gender, $age, $country, $county, $skills, $education, $salary, $id];
    $ok = $stmt->execute($params);

    echo $ok ? 'Employee updated successfully.' : 'Failed to update employee.';
    exit;
}

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
        .btn-edit { background: #2ecc71; color: white; } /* Edit button : jean luc 26 SEP 25 */
        .btn-edit:hover { background: #27ae60; }
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
        /* Modal styles : jean luc 26 SEP 25 */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            justify-content: center;
            align-items: center;
            padding: 40px 0; /* Add top and bottom spacing : jean luc 26 SEP 25 */
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 500px;
            max-width: 95%;
            max-height: 80vh; /* Prevent overflow : jean luc 26 SEP 25 */
            overflow-y: auto; /* Scroll inner fields if content is long : jean luc 26 SEP 25 */
            position: relative; /* Position anchor for close button : jean luc 26 SEP 25 */
        }
        .modal-content h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        .modal-content label {
            display: block;
            margin: 8px 0 4px;
        }
        .modal-content input, .modal-content select, .modal-content textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        .modal-close {
            position: absolute; /* Place at top-right : jean luc 26 SEP 25 */
            top: 10px; right: 10px;
            width: 32px; height: 32px;
            border-radius: 50%;
            background: #3498db; /* Blue circle : jean luc 26 SEP 25 */
            color: red; /* Red X : jean luc 26 SEP 25 */
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            line-height: 32px;
            cursor: pointer;
        }
        .modal-close:hover {
            background: #2980b9; /* Darker blue on hover : jean luc 26 SEP 25 */
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
                                <button type="submit" class="btn-action btn-toggle">Change Verification Status</button>
                            </form>

                            <!-- Edit : jean luc 26 SEP 25 -->
                            <button type="button" class="btn-action btn-edit" onclick="openEditModal(<?= $emp['ID'] ?>)">Edit</button>

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

<!-- Edit Modal : jean luc 26 SEP 25 -->
<div id="editModal" class="modal" aria-hidden="true" role="dialog">
    <div class="modal-content" role="document">
        <span class="modal-close" onclick="closeEditModal()" aria-label="Close">&times;</span>
        <h3>Edit Employee</h3>
        <form id="editEmployeeForm">
            <input type="hidden" name="employee_id" id="edit_id">
            <label for="edit_name">Name</label>
            <input type="text" name="Name" id="edit_name" required>
            <label for="edit_email">Email</label>
            <input type="email" name="Email" id="edit_email" required>
            <label for="edit_phone">Phone</label>
            <input type="text" name="Phone" id="edit_phone" required>
            <label for="edit_gender">Gender</label>
            <select name="Gender" id="edit_gender">
                <option value="">-- Select --</option>
                <option value="male">male</option>
                <option value="female">female</option>
            </select>
            <label for="edit_age">Age</label>
            <input type="number" name="Age" id="edit_age" min="0">
            <label for="edit_country">Country</label>
            <input type="text" name="Country" id="edit_country">
            <label for="edit_county">County</label>
            <input type="text" name="County_province" id="edit_county">
            <label for="edit_skills">Skills</label>
            <input type="text" name="Skills" id="edit_skills">
            <label for="edit_education">Education Level</label>
            <input type="text" name="Education_level" id="edit_education">
            <label for="edit_salary">Salary Expectation</label>
            <input type="text" name="salary_expectation" id="edit_salary">
            <button type="submit" class="btn-action btn-edit" style="margin-top:10px;">Save Changes</button>
        </form>
    </div>
</div>

<script>
// Open modal and fetch employee details : jean luc 26 SEP 25
function openEditModal(id) {
    var modal = document.getElementById('editModal');
    fetch('manage_employees.php?ajax=get_employee&id=' + encodeURIComponent(id), { credentials: 'same-origin' })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (!data || !data.ID) {
                alert('Employee not found.');
                return;
            }
            document.getElementById('edit_id').value = data.ID;
            document.getElementById('edit_name').value = data.Name || '';
            document.getElementById('edit_email').value = data.Email || '';
            document.getElementById('edit_phone').value = data.Phone || '';
            document.getElementById('edit_gender').value = data.Gender || '';
            document.getElementById('edit_age').value = (data.Age !== null && data.Age !== undefined) ? data.Age : '';
            document.getElementById('edit_country').value = data.Country || '';
            document.getElementById('edit_county').value = data.County_province || '';
            document.getElementById('edit_skills').value = data.Skills || '';
            document.getElementById('edit_education').value = data.Education_level || '';
            document.getElementById('edit_salary').value = data.salary_expectation || '';

            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        })
        .catch(function(err) {
            console.error(err);
            alert('Failed to fetch employee details.');
        });
}

function closeEditModal() {
    var modal = document.getElementById('editModal');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
}

// Close modal when clicking outside content
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

// Handle form submit : jean luc 26 SEP 25
document.getElementById('editEmployeeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var formData = new FormData(form);
    formData.append('ajax', 'update_employee');

    fetch('manage_employees.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(function(res) { return res.text(); })
    .then(function(text) {
        // Show server message then reload so table reflects changes
        alert(text);
        if (text.toLowerCase().indexOf('success') !== -1) {
            window.location.reload();
        }
    })
    .catch(function(err) {
        console.error(err);
        alert('Failed to update employee.');
    });
});

// Optional: close modal on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var modal = document.getElementById('editModal');
        if (modal.style.display === 'flex') closeEditModal();
    }
});
</script>
</body>
</html>