<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "houselp_db");
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $conn->prepare("DELETE FROM bookings WHERE ID = ?");
$stmt->bind_param("i", $data["ID"]);

if ($stmt->execute()) {
    echo json_encode(["message" => "Booking deleted successfully"]);
} else {
    echo json_encode(["error" => "Failed to delete booking"]);
}

$conn->close();
?>
