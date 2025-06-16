<?php
include 'db.php';

$ID = $_POST['ID'];

$stmt = $conn->prepare("DELETE FROM review_table WHERE ID = ?");
$stmt->bind_param("i", $ID);

if ($stmt->execute()) {
    echo "Review deleted successfully.";
} else {
    echo "Error: " . $stmt->error;
}
?>
