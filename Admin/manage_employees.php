<?php
session_start();
require_once '../db_connect.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
$success = '';
$error = '';
// Handle status updates and deletion
if (isset($_POST['action'], $_POST['employee_id'])) {
    $employee_id = $_POST['employee_id'];
    $action = $_POST['action'];
    if ($action === 'delete') {
        $stmt = $conn->prepare('DELETE FROM employees WHERE id = ?');
        if ($stmt->execute([$employee_id])) {
            $success = 'Employee deleted successfully.';
        } else {
            $error = 'Failed to delete employee.';
        }
    } elseif (in_array($action, ['activate', 'deactivate'])) {
        $new_status = $action === 'activate' ? 'active' : 'inactive';
        $stmt = $conn->prepare('UPDATE employees SET status = ? WHERE id = ?');
        if ($stmt->execute([$new_status, $employee_id])) {
            $success = 'Employee status updated.';
        } else {
            $error = 'Failed to update status.';
        }
    }
}
// Fetch all employees
$stmt = $conn->query('SELECT * FROM employees ORDER BY created_at DESC');
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Employees</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<div class="container">
    <h1>Manage Employees</h1>
    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Registered</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($employees as $emp): ?>
            <tr>
                <td><?= $emp['id'] ?></td>
                <td><?= htmlspecialchars($emp['name']) ?></td>
                <td><?= htmlspecialchars($emp['email']) ?></td>
                <td><?= htmlspecialchars($emp['status']) ?></td>
                <td><?= htmlspecialchars($emp['created_at']) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="employee_id" value="<?= $emp['id'] ?>">
                        <input type="hidden" name="action" value="<?= $emp['status'] === 'active' ? 'deactivate' : 'activate' ?>">
                        <button type="submit"><?= $emp['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="employee_id" value="<?= $emp['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" onclick="return confirm('Delete this employee?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html> 