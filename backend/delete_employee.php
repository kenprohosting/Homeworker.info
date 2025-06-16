<?php
include 'db.php';

$ID = $_POST['ID'];

$sql = "DELETE FROM employee WHERE ID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ID);

if ($stmt->execute()) {
    echo json_encode(["message" => "Employee deleted successfully"]);
} else {
    echo json_encode(["message" => "Delete failed"]);
}
?>
