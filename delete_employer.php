<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$ID = $data['ID'];

$sql = "DELETE FROM employer WHERE ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ID);

if ($stmt->execute()) {
    echo json_encode(["message" => "Employer deleted successfully"]);
} else {
    echo json_encode(["message" => "Deletion failed"]);
}

$conn->close();
?>
