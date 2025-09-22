<?php
session_start();
require_once '../db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';
$generated_code = '';
$generated_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_code'])) {
    // Get the next available agent ID
    $stmt = $conn->query("SELECT MAX(agent_id) AS max_id FROM agent_registration_codes");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $new_agent_id = $row['max_id'] ? $row['max_id'] + 1 : 1001;

    // Generate a random registration code
    $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    $reg_code = 'AGENT' . $new_agent_id . $random;

    // Insert into the database
    $stmt = $conn->prepare("INSERT INTO agent_registration_codes (code, agent_id, description, status, assigned_to) VALUES (?, ?, ?, 'active', ?)");
    $desc = 'Auto-generated code';
    $assigned_to = '';
    try {
        $stmt->execute([$reg_code, $new_agent_id, $desc, $assigned_to]);
        $success = 'Agent ID and registration code generated successfully!';
        $generated_code = $reg_code;
        $generated_id = $new_agent_id;
    } catch (Exception $e) {
        $error = 'Error generating code: ' . $e->getMessage();
    }
}

/* join agents to get agent name when displaying assigned_to : jean luc 22 SEP 25 */
$stmt = $conn->prepare("
    SELECT arc.code, arc.agent_id, arc.description, arc.status, arc.assigned_to, arc.created_at, arc.used_at,
           a.name AS agent_name
    FROM agent_registration_codes arc
    LEFT JOIN agents a ON arc.agent_id = a.id
    ORDER BY arc.created_at DESC
");
$stmt->execute();
$codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta charset="UTF-8">
    <title>Manage Agent Registration Codes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <style>
        .admin-container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .admin-header { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 30px 20px; text-align: center; margin-bottom: 30px; border-radius: 10px; }
        .admin-nav { background: white; padding: 15px 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .admin-nav ul { list-style: none; padding: 0; margin: 0; display: flex; gap: 20px; flex-wrap: wrap; }
        .admin-nav a { color: #2c3e50; text-decoration: none; padding: 10px 15px; border-radius: 5px; transition: background 0.3s; }
        .admin-nav a:hover { background: #f8f9fa; }
        .admin-nav a.active { background: #3498db; color: white; }
        .form-section { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .form-section h3 { color: #2c3e50; margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .btn { background: #3498db; color: white; padding: 12px 25px; border: none; border-radius: 25px; font-weight: bold; cursor: pointer; transition: background-color 0.3s; }
        .btn:hover { background: #2980b9; }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 15px; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 15px; }
        .generated-info { background: #e8f4fd; color: #197b88; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 1.1rem; text-align: center; }
        .codes-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .codes-table th, .codes-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .codes-table th { background: #f8f9fa; font-weight: bold; color: #2c3e50; }
        .codes-table tr:hover { background: #f8f9fa; }
        .status-active { background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; }
        .status-used { background: #cce5ff; color: #004085; padding: 3px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; }
        .status-revoked { background: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Manage Agent Registration Codes</h1>
            <p>Generate and manage agent registration codes and IDs</p>
        </div>
        <div class="admin-nav">
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="manage_agents.php">Manage Agents</a></li>
                <li><a href="manage_codes.php" class="active">Registration Codes</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="logout.php" style="color: #e74c3c;">Logout</a></li>
            </ul>
        </div>
        <div class="form-section">
            <h3>Generate New Agent ID & Registration Code</h3>
            <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
            <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
            <form method="POST" action="">
                <button type="submit" name="generate_code" class="btn">Generate New Code</button>
            </form>
            <?php if ($generated_code && $generated_id): ?>
                <div class="generated-info">
                    <strong>Agent ID:</strong> <?= htmlspecialchars($generated_id) ?><br>
                    <strong>Registration Code:</strong> <?= htmlspecialchars($generated_code) ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="form-section">
            <h3>All Registration Codes</h3>
            <table class="codes-table">
                <thead>
                    <tr>
                        <th>Agent ID</th>
                        <th>Code</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Description</th>
                        <th>Created</th>
                        <th>Used</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($codes as $code): ?>
                        <tr>
                            <td><?= htmlspecialchars($code['agent_id']) ?></td>
                            <td><?= htmlspecialchars($code['code']) ?></td>
                            <td><span class="status-<?= $code['status'] ?>"><?= ucfirst($code['status']) ?></span></td>
                            <td>
                                <?php if ($code['status'] === 'used' && $code['agent_name']): ?>
                                    <?= htmlspecialchars($code['agent_name']) ?>
                                    <!-- show agent id and name when code is used : jean luc 22 SEP 25 -->
                                <?php else: ?>
                                    Unassigned
                                    <!-- show Unassigned when code is still active but not used : jean luc 22 SEP 25 -->
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($code['description']) ?></td>
                            <td><?= date('M j, Y', strtotime($code['created_at'])) ?></td>
                            <td><?= $code['used_at'] ? date('M j, Y', strtotime($code['used_at'])) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
