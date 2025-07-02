<?php
session_start();

// Auto logout after 10 mins
$timeout_duration = 600;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: index.php?timeout=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}
require_once("../db_connect.php");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <style>
    body { font-family: Arial; background: #f4f4f4; padding: 20px; }
    h2 { color: #00695c; }
    .section { background: white; padding: 20px; margin-bottom: 30px; border-radius: 8px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background-color: #eee; }
    .btn {
      padding: 5px 10px;
      border: none;
      background: #00695c;
      color: white;
      border-radius: 3px;
      text-decoration: none;
    }
    .danger {
      background: red;
    }
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
  </style>
</head>
<body>

<div class="topbar">
  <h2>Admin Control Panel</h2>
  <a href="admin_logout.php" class="btn danger">Logout</a>
</div>

<!-- Section: Employees -->
<div class="section">
  <h3>All Employees</h3>
  <?php
  $stmt = $conn->query("SELECT * FROM employee ORDER BY ID DESC");
  $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>
  <table>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Email</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
    <?php foreach ($employees as $emp): ?>
    <tr>
      <td><?= $emp['ID'] ?></td>
      <td><?= htmlspecialchars($emp['Name']) ?></td>
      <td><?= htmlspecialchars($emp['email'] ?? 'N/A') ?></td>
      <td><?= htmlspecialchars($emp['verification_status']) ?></td>
      <td>
        <a href="verify_employee.php?id=<?= $emp['ID'] ?>" class="btn"
           onclick="return confirm('Are you sure you want to verify/unverify this employee?');">
           Toggle Verify
        </a>
        <a href="delete_employee.php?id=<?= $emp['ID'] ?>" class="btn danger"
           onclick="return confirm('Are you sure you want to permanently delete this employee?');">
           Delete
        </a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

<!-- Section: Employers -->
<div class="section">
  <h3>All Employers</h3>
  <?php
  $stmt = $conn->query("SELECT * FROM employer ORDER BY ID DESC");
  $employers = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>
  <table>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Email</th>
      <th>Action</th>
    </tr>
    <?php foreach ($employers as $e): ?>
    <tr>
      <td><?= $e['ID'] ?></td>
      <td><?= htmlspecialchars($e['Name']) ?></td>
      <td><?= htmlspecialchars($e['email'] ?? 'N/A') ?></td>
      <td>
        <a href="delete_employer.php?id=<?= $e['ID'] ?>" class="btn danger"
           onclick="return confirm('Are you sure you want to permanently delete this employer?');">
           Delete
        </a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

</body>
</html>
