<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}
require_once("../db_connect.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM employee WHERE ID = ?");
    $stmt->execute([$id]);
}
header("Location: admin_dashboard.php");
exit();
