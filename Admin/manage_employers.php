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

// AJAX endpoint for fetching employer details
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_employer' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $conn->prepare("SELECT ID, Name, Email, Contact, Gender, Location, Residence_type, Country FROM employer WHERE ID = ?");
    $stmt->execute([$id]);
    $employer = $stmt->fetch(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($employer ?: []);
    exit;
}

// AJAX endpoint for fetching employer summary details
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_employer_summary' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    
    // Get employer details
    $stmt = $conn->prepare("SELECT ID, Name, Email, Contact, Gender, Location, Residence_type, Country, Verification_status, Created_at FROM employer WHERE ID = ?");
    $stmt->execute([$id]);
    $employer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($employer ?: []);
    exit;
}

// AJAX endpoint for updating employer details
if (isset($_POST['ajax']) && $_POST['ajax'] === 'update_employer') {
    if (!isset($_SESSION['admin_id'])) {
        echo 'Unauthorized';
        exit;
    }

    $id = isset($_POST['employer_id']) ? (int) $_POST['employer_id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $residence_type = trim($_POST['residence_type'] ?? '');
    $country = trim($_POST['country'] ?? '');

    if ($id <= 0 || $name === '' || $email === '') {
        echo 'Invalid input. Name and email are required.';
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo 'Invalid email address.';
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE employer SET Name = ?, Email = ?, Contact = ?, Gender = ?, Location = ?, Residence_type = ?, Country = ? WHERE ID = ?");
        $params = [$name, $email, $contact, $gender, $location, $residence_type, $country, $id];
        $ok = $stmt->execute($params);

        if ($ok) {
            echo 'Employer updated successfully.';
        } else {
            echo 'Failed to update employer.';
        }
    } catch (Exception $ex) {
        echo 'Failed to update employer.';
    }
    exit;
}

// Handle verification toggle and deletion : jean luc 26 SEP 25
if (isset($_POST['action'], $_POST['employer_id'])) {
    $employer_id = $_POST['employer_id'];
    $action = $_POST['action'];

    if ($action === 'delete') {
        // Delete employer : jean luc 26 SEP 25
        $stmt = $conn->prepare('DELETE FROM employer WHERE ID = ?');
        if ($stmt->execute([$employer_id])) {
            $success = 'Employer deleted successfully.';
        } else {
            $error = 'Failed to delete employer.';
        }
    } elseif ($action === 'toggle_verification') {
        // Toggle verification status : jean luc 26 SEP 25
        $stmt = $conn->prepare('SELECT Verification_status FROM employer WHERE ID = ?');
        $stmt->execute([$employer_id]);
        $current_status = $stmt->fetchColumn();

        // Cycle unverified → pending → verified → unverified : jean luc 26 SEP 25
        if ($current_status === 'unverified') {
            $new_status = 'pending';
        } elseif ($current_status === 'pending') {
            $new_status = 'verified';
        } else {
            $new_status = 'unverified';
        }

        $stmt = $conn->prepare('UPDATE employer SET Verification_status = ? WHERE ID = ?');
        if ($stmt->execute([$new_status, $employer_id])) {
            $success = "Verification status changed to $new_status.";
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
 $totalEmployers = $conn->query('SELECT COUNT(*) FROM employer')->fetchColumn();
 $totalPages = ceil($totalEmployers / $perPage);

// Fetch employers with pagination
 $stmt = $conn->prepare("
    SELECT 
        ID, Name, Email, Contact, Gender, Location, Residence_type, Country, Verification_status, Created_at
    FROM employer
    ORDER BY Created_at DESC
    LIMIT $perPage OFFSET $offset
");
 $stmt->execute();
 $employers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta charset="UTF-8">
    <title>Manage Employers - Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
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

        .employers-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            min-width: 1000px;
        }
        .employers-table th, .employers-table td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid var(--table-border);
            vertical-align: middle;
            white-space: nowrap;
        }
        .employers-table th {
            background: #f8f9fa;
            font-weight: 700;
            color: #2c3e50;
            font-size: 0.95rem;
        }
        .employers-table tr:hover { background: #fbfdfe; }

        .employer-name {
            color: var(--blue);
            cursor: pointer;
            text-decoration: underline;
        }
        .employer-name:hover {
            color: var(--accent-1);
        }

        .action-buttons { 
            display: flex; 
            gap: 8px; 
            align-items: center;
        }

        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
        }
        .btn-edit { background:#2ecc71; color:#fff; }
        .btn-edit:hover { background:#27ae60; }
        .btn-delete { background:#e74c3c; color:#fff; }
        .btn-delete:hover { background:#c0392b; }
        .btn-toggle { background:#3498db; color:#fff; }
        .btn-toggle:hover { background:#2980b9; }

        .verification-unverified { color:red; font-weight:700; }
        .verification-pending { color:orange; font-weight:700; }
        .verification-verified { color:green; font-weight:700; }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        /* Pagination */
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

        /* Modal styles */
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
            width: 500px;
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
        .form-group { margin-bottom:15px; }
        .form-group label { display:block; font-weight:600; margin-bottom:6px; }
        .form-group input[type="text"], .form-group input[type="email"], .form-group select {
            width:100%; padding:9px; border:1px solid #dfe7ea; border-radius:6px; box-sizing:border-box;
        }
        .form-group .required { color: #e74c3c; }

        .modal-buttons {
            margin-top: 20px;
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

        /* Summary Modal Styles */
        .summary-modal .modal-content {
            width: 600px;
        }
        .summary-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--blue);
        }
        .summary-header h3 {
            margin: 0;
            color: var(--blue);
        }
        .summary-info {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 12px;
        }
        .summary-label {
            font-weight: 600;
            color: #2c3e50;
        }
        .summary-value {
            color: #333;
        }
        .verification-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .verification-badge.unverified {
            background: #f8d7da;
            color: #721c24;
        }
        .verification-badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        .verification-badge.verified {
            background: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="admin-header">
        <h1>Manage Employers</h1>
        <p>View and manage registered employers</p>
    </div>

    <div class="admin-nav">
        <ul>
            <!-- Admin navigation : jean luc 26 SEP 25 -->
            <li><a href="https://homeworker.info/" style="color: #e74c3c;">Back</a></li>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="manage_agents.php">Manage Agents</a></li>
            <li><a href="manage_employees.php">Manage Employees</a></li>
            <li><a href="manage_employers.php" class="active">Manage Employers</a></li>
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
        <h3>Registered Employers (<?= $totalEmployers ?>)</h3>

        <?php if (count($employers) > 0): ?>
        <table class="employers-table">
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Contact</th>
                    <th>Gender</th><th>Location</th><th>Residence</th><th>Country</th>
                    <th>Verification</th><th>Registered</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employers as $emp): ?>
                <tr>
                    <td><?= htmlspecialchars($emp['ID']) ?></td>
                    <td><strong class="employer-name" onclick="openSummaryModal(<?= (int)$emp['ID'] ?>)"><?= htmlspecialchars($emp['Name']) ?></strong></td>
                    <td><?= htmlspecialchars($emp['Email']) ?></td>
                    <td><?= htmlspecialchars($emp['Contact']) ?></td>
                    <td><?= htmlspecialchars($emp['Gender']) ?></td>
                    <td><?= htmlspecialchars($emp['Location']) ?></td>
                    <td><?= htmlspecialchars($emp['Residence_type']) ?></td>
                    <td><?= htmlspecialchars($emp['Country']) ?></td>
                    <td class="verification-<?= htmlspecialchars($emp['Verification_status'] ?: 'unverified') ?>">
                        <?= htmlspecialchars($emp['Verification_status'] ?: 'unverified') ?>
                    </td>
                    <td><?= date('M j, Y', strtotime($emp['Created_at'])) ?></td>
                    <td>
                        <div class="action-buttons">
                            <!-- Edit -->
                            <button type="button" class="btn-action btn-edit" onclick="openEditModal(<?= (int)$emp['ID'] ?>)">Edit</button>
                            
                            <!-- Toggle verification -->
                            <form method="POST">
                                <input type="hidden" name="employer_id" value="<?= $emp['ID'] ?>">
                                <input type="hidden" name="action" value="toggle_verification">
                                <button type="submit" class="btn-action btn-toggle">Change Verification Status</button>
                            </form>

                            <!-- Delete -->
                            <form method="POST" onsubmit="return confirm('Delete this employer?')">
                                <input type="hidden" name="employer_id" value="<?= $emp['ID'] ?>">
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
                <h4>No Employers Registered</h4>
                <p>Employers will appear here once they register.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal" aria-hidden="true" role="dialog" aria-labelledby="editModalTitle">
    <div class="modal-content" role="document">
        <div class="modal-close" onclick="closeEditModal()" title="Close">&times;</div>
        <h3 id="editModalTitle">Edit Employer</h3>

        <form id="editEmployerForm" autocomplete="off" novalidate>
            <input type="hidden" name="employer_id" id="edit_id">
            
            <div class="form-group">
                <label for="edit_name">Full Name <span class="required">*</span></label>
                <input type="text" name="name" id="edit_name" required>
            </div>
            
            <div class="form-group">
                <label for="edit_email">Email Address <span class="required">*</span></label>
                <input type="email" name="email" id="edit_email" required>
            </div>
            
            <div class="form-group">
                <label for="edit_contact">Contact Number</label>
                <input type="text" name="contact" id="edit_contact">
            </div>
            
            <div class="form-group">
                <label for="edit_gender">Gender</label>
                <select name="gender" id="edit_gender">
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="edit_location">Location</label>
                <input type="text" name="location" id="edit_location">
            </div>
            
            <div class="form-group">
                <label for="edit_residence_type">Residence Type</label>
                <select name="residence_type" id="edit_residence_type">
                    <option value="">Select Residence Type</option>
                    <option value="urban">Urban</option>
                    <option value="rural">Rural</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="edit_country">Country</label>
                <input type="text" name="country" id="edit_country">
            </div>

            <div class="modal-buttons">
                <button type="submit" class="btn-save">Save Changes</button>
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Modal -->
<div id="summaryModal" class="modal summary-modal" aria-hidden="true" role="dialog" aria-labelledby="summaryModalTitle">
    <div class="modal-content" role="document">
        <div class="modal-close" onclick="closeSummaryModal()" title="Close">&times;</div>
        <div class="summary-header">
            <h3 id="summaryModalTitle">Employer Information Summary</h3>
        </div>
        
        <div class="summary-info">
            <div class="summary-label">Name:</div>
            <div class="summary-value" id="summary_name"></div>
            
            <div class="summary-label">Email:</div>
            <div class="summary-value" id="summary_email"></div>
            
            <div class="summary-label">Contact:</div>
            <div class="summary-value" id="summary_contact"></div>
            
            <div class="summary-label">Gender:</div>
            <div class="summary-value" id="summary_gender"></div>
            
            <div class="summary-label">Location:</div>
            <div class="summary-value" id="summary_location"></div>
            
            <div class="summary-label">Residence Type:</div>
            <div class="summary-value" id="summary_residence_type"></div>
            
            <div class="summary-label">Country:</div>
            <div class="summary-value" id="summary_country"></div>
            
            <div class="summary-label">Verification Status:</div>
            <div class="summary-value">
                <span id="summary_verification_status" class="verification-badge"></span>
            </div>
            
            <div class="summary-label">Registration Date:</div>
            <div class="summary-value" id="summary_created_at"></div>
        </div>
        
        <div class="modal-buttons">
            <button type="button" class="btn-cancel" onclick="closeSummaryModal()">Close</button>
        </div>
    </div>
</div>

<script>
/**
 * Open edit modal and populate values via AJAX.
 */
function openEditModal(id) {
    var modal = document.getElementById('editModal');
    fetch('manage_employers.php?ajax=get_employer&id=' + encodeURIComponent(id), { credentials: 'same-origin' })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (!data || !data.ID) {
                alert('Employer not found.');
                return;
            }

            // Fill inputs
            document.getElementById('edit_id').value = data.ID || '';
            document.getElementById('edit_name').value = data.Name || '';
            document.getElementById('edit_email').value = data.Email || '';
            document.getElementById('edit_contact').value = data.Contact || '';
            document.getElementById('edit_gender').value = data.Gender || '';
            document.getElementById('edit_location').value = data.Location || '';
            document.getElementById('edit_residence_type').value = data.Residence_type || '';
            document.getElementById('edit_country').value = data.Country || '';

            // Show modal
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        })
        .catch(function(err) {
            console.error(err);
            alert('Failed to fetch employer details.');
        });
}

/**
 * Close edit modal
 */
function closeEditModal() {
    var modal = document.getElementById('editModal');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
}

/**
 * Open summary modal and populate values via AJAX.
 */
function openSummaryModal(id) {
    var modal = document.getElementById('summaryModal');
    fetch('manage_employers.php?ajax=get_employer_summary&id=' + encodeURIComponent(id), { credentials: 'same-origin' })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (!data || !data.ID) {
                alert('Employer not found.');
                return;
            }

            // Update modal title with employer name
            document.getElementById('summaryModalTitle').textContent = 'Employer ' + data.Name + ' Information Summary';
            
            // Fill summary fields
            document.getElementById('summary_name').textContent = data.Name || '';
            document.getElementById('summary_email').textContent = data.Email || '';
            document.getElementById('summary_contact').textContent = data.Contact || '';
            document.getElementById('summary_gender').textContent = data.Gender || '';
            document.getElementById('summary_location').textContent = data.Location || '';
            document.getElementById('summary_residence_type').textContent = data.Residence_type || '';
            document.getElementById('summary_country').textContent = data.Country || '';
            
            // Verification status with badge styling
            var verificationStatus = document.getElementById('summary_verification_status');
            verificationStatus.textContent = data.Verification_status || 'unverified';
            verificationStatus.className = 'verification-badge ' + (data.Verification_status || 'unverified');
            
            // Registration date
            document.getElementById('summary_created_at').textContent = data.Created_at ? new Date(data.Created_at).toLocaleString() : '';

            // Show modal
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        })
        .catch(function(err) {
            console.error(err);
            alert('Failed to fetch employer details.');
        });
}

/**
 * Close summary modal
 */
function closeSummaryModal() {
    var modal = document.getElementById('summaryModal');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
}

/**
 * Submit edit form via AJAX
 */
document.getElementById('editEmployerForm').addEventListener('submit', function(e) {
    e.preventDefault();

    var form = this;
    var formData = new FormData(form);
    formData.append('ajax', 'update_employer');

    fetch('manage_employers.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    })
    .then(function(res) { return res.text(); })
    .then(function(text) {
        alert(text);
        if (text && text.toLowerCase().indexOf('success') !== -1) {
            // reload to show updated table
            window.location.reload();
        }
    })
    .catch(function(err) {
        console.error(err);
        alert('Failed to update employer.');
    });
});

// Close modal when clicking outside of content
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

document.getElementById('summaryModal').addEventListener('click', function(e) {
    if (e.target === this) closeSummaryModal();
});

// Close modal on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var editModal = document.getElementById('editModal');
        var summaryModal = document.getElementById('summaryModal');
        
        if (editModal && editModal.style.display === 'flex') closeEditModal();
        if (summaryModal && summaryModal.style.display === 'flex') closeSummaryModal();
    }
});
</script>
</body>
</html>