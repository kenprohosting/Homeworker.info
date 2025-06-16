<?php
include 'db.php';

$sql = "SELECT * FROM employee";
$result = $conn->query($sql);

$employees = array();

while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}

header('Content-Type: application/json');
echo json_encode($employees);
?>
