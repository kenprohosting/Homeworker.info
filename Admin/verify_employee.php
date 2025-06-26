<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}
require_once("../db_connect.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT verification_status FROM employee WHERE ID = ?");
    $stmt->execute([$id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employee) {
        $newStatus = $employee['verification_status'] === 'verified' ? 'unverified' : 'verified';
        $update = $conn->prepare("UPDATE employee SET verification_status = ? WHERE ID = ?");
        $update->execute([$newStatus, $id]);
    }
}
header("Location: admin_dashboard.php");
exit();
