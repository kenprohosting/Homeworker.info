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
    }
}
// Fetch all employees
$stmt = $conn->query('SELECT ID, Name, Email, National_id, Status, Created_at FROM employees ORDER BY Created_at DESC');
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
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
                <th>ID</th><th>Name</th><th>Email</th><th>National ID</th><th>Status</th><th>Registered</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($employees as $emp): ?>
            <tr>
                <td><?= $emp['ID'] ?></td>
                <td><?= htmlspecialchars($emp['Name']) ?></td>
                <td><?= htmlspecialchars($emp['Email']) ?></td>
                <td><?= htmlspecialchars($emp['National_id']) ?></td>
                <td><?= htmlspecialchars($emp['Status']) ?></td>
                <td><?= htmlspecialchars($emp['Created_at']) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="employee_id" value="<?= $emp['ID'] ?>">
                        <input type="hidden" name="action" value="<?= $emp['Status'] === 'active' ? 'deactivate' : 'activate' ?>">
                        <button type="submit"><?= $emp['Status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="employee_id" value="<?= $emp['ID'] ?>">
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