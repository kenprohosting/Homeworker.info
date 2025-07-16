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
if (isset($_POST['action'], $_POST['employer_id'])) {
    $employer_id = $_POST['employer_id'];
    $action = $_POST['action'];
    if ($action === 'delete') {
        $stmt = $conn->prepare('DELETE FROM employer WHERE id = ?');
        if ($stmt->execute([$employer_id])) {
            $success = 'Employer deleted successfully.';
        } else {
            $error = 'Failed to delete employer.';
        }
    } elseif (in_array($action, ['activate', 'deactivate'])) {
        $new_status = $action === 'activate' ? 'active' : 'inactive';
        $stmt = $conn->prepare('UPDATE employer SET verification_status = ? WHERE id = ?');
        if ($stmt->execute([$new_status, $employer_id])) {
            $success = 'Employer status updated.';
        } else {
            $error = 'Failed to update status.';
        }
    }
}
// Fetch all employers
$stmt = $conn->query('SELECT * FROM employer ORDER BY created_at DESC');
$employers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Employers</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<div class="container">
    <h1>Manage Employers</h1>
    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Registered</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($employers as $emp): ?>
            <tr>
                <td><?= $emp['ID'] ?></td>
                <td><?= htmlspecialchars($emp['Name']) ?></td>
                <td><?= htmlspecialchars($emp['email']) ?></td>
                <td><?= htmlspecialchars($emp['verification_status']) ?></td>
                <td><?= htmlspecialchars($emp['created_at']) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="employer_id" value="<?= $emp['ID'] ?>">
                        <input type="hidden" name="action" value="<?= $emp['verification_status'] === 'active' ? 'deactivate' : 'activate' ?>">
                        <button type="submit"><?= $emp['verification_status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="employer_id" value="<?= $emp['ID'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" onclick="return confirm('Delete this employer?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html> 