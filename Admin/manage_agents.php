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

// Handle AJAX request to get agent details
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_agent' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $conn->prepare("SELECT id, name, email, national_id, phone FROM agents WHERE id = ?");
    $stmt->execute([$id]);
    $agent = $stmt->fetch(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($agent ?: []);
    exit;
}

// Handle AJAX request to update agent details
if (isset($_POST['ajax']) && $_POST['ajax'] === 'update_agent') {
    if (!isset($_SESSION['admin_id'])) {
        echo 'Unauthorized';
        exit;
    }

    $id = isset($_POST['agent_id']) ? (int) $_POST['agent_id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $national_id = trim($_POST['national_id'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($id <= 0 || $name === '' || $email === '') {
        echo 'Invalid input. Name and email are required.';
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo 'Invalid email address.';
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE agents SET name = ?, email = ?, national_id = ?, phone = ? WHERE id = ?");
        $params = [$name, $email, $national_id, $phone, $id];
        $ok = $stmt->execute($params);

        if ($ok) {
            echo 'Agent updated successfully.';
        } else {
            echo 'Failed to update agent.';
        }
    } catch (Exception $ex) {
        echo 'Failed to update agent.';
    }
    exit;
}

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

// Simple pagination
 $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
 $perPage = 10;
 $offset = ($page - 1) * $perPage;

// Get total count
 $totalAgents = $conn->query('SELECT COUNT(*) FROM agents')->fetchColumn();
 $totalPages = ceil($totalAgents / $perPage);

// Get agents with pagination
 $stmt = $conn->prepare("SELECT id, name, email, national_id, phone, created_at FROM agents ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
 $stmt->execute();
 $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta charset="UTF-8">
    <title>Manage Agents - Admin Dashboard</title>
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

        .agents-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }
        .agents-table th, .agents-table td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid var(--table-border);
            vertical-align: middle;
        }
        .agents-table th {
            background: #f8f9fa;
            font-weight: 700;
            color: #2c3e50;
            font-size: 0.95rem;
        }
        .agents-table tr:hover { background: #fbfdfe; }

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
        .form-group input[type="text"], .form-group input[type="email"], .form-group input[type="tel"] {
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
                <!-- Add back button to main site : jean luc 22 SEP 25 -->
                <li><a href="https://homeworker.info/" style="color: #e74c3c;">Back</a></li>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="manage_agents.php" class="active">Manage Agents</a></li>
                <li><a href="manage_employees.php">Manage Employees</a></li>
                <li><a href="manage_employers.php">Manage Employers</a></li>
                <li><a href="manage_codes.php">Registration Codes</a></li>
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
            <h3>Registered Agents (<?= $totalAgents ?>)</h3>
            
            <?php if (count($agents) > 0): ?>
                <table class="agents-table">
                    <thead>
                        <tr>
                            <th>Agent ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>National ID</th>
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
                                <td><?= htmlspecialchars($agent['national_id']) ?></td>
                                <td><?= htmlspecialchars($agent['phone']) ?></td>
                                <td><?= date('M j, Y', strtotime($agent['created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="btn-action btn-edit" onclick="openEditModal(<?= (int)$agent['id'] ?>)">Edit</button>
                                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this agent?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="agent_id" value="<?= $agent['id'] ?>">
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
                    <h4>No Agents Registered</h4>
                    <p>No agents have registered yet. Agents will appear here once they complete registration.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal" aria-hidden="true" role="dialog" aria-labelledby="editModalTitle">
        <div class="modal-content" role="document">
            <div class="modal-close" onclick="closeEditModal()" title="Close">&times;</div>
            <h3 id="editModalTitle">Edit Agent</h3>

            <form id="editAgentForm" autocomplete="off" novalidate>
                <input type="hidden" name="agent_id" id="edit_id">
                
                <div class="form-group">
                    <label for="edit_name">Full Name <span class="required">*</span></label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email Address <span class="required">*</span></label>
                    <input type="email" name="email" id="edit_email" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_national_id">National ID</label>
                    <input type="text" name="national_id" id="edit_national_id">
                </div>
                
                <div class="form-group">
                    <label for="edit_phone">Phone Number</label>
                    <input type="tel" name="phone" id="edit_phone">
                </div>

                <div class="modal-buttons">
                    <button type="submit" class="btn-save">Save Changes</button>
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        /**
         * Open edit modal and populate values via AJAX.
         */
        function openEditModal(id) {
            var modal = document.getElementById('editModal');
            fetch('manage_agents.php?ajax=get_agent&id=' + encodeURIComponent(id), { credentials: 'same-origin' })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (!data || !data.id) {
                        alert('Agent not found.');
                        return;
                    }

                    // Fill inputs
                    document.getElementById('edit_id').value = data.id || '';
                    document.getElementById('edit_name').value = data.name || '';
                    document.getElementById('edit_email').value = data.email || '';
                    document.getElementById('edit_national_id').value = data.national_id || '';
                    document.getElementById('edit_phone').value = data.phone || '';

                    // Show modal
                    modal.style.display = 'flex';
                    modal.setAttribute('aria-hidden', 'false');
                })
                .catch(function(err) {
                    console.error(err);
                    alert('Failed to fetch agent details.');
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
         * Submit edit form via AJAX
         */
        document.getElementById('editAgentForm').addEventListener('submit', function(e) {
            e.preventDefault();

            var form = this;
            var formData = new FormData(form);
            formData.append('ajax', 'update_agent');

            fetch('manage_agents.php', {
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
                alert('Failed to update agent.');
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
    </script>
</body>
</html>