<?php
include 'db.php';

$ID = $_POST['ID'];

$stmt = $conn->prepare("DELETE FROM payment WHERE ID = ?");
$stmt->bind_param("i", $ID);

if ($stmt->execute()) {
    echo "Payment deleted successfully.";
} else {
    echo "Error: " . $stmt->error;
}
?>
