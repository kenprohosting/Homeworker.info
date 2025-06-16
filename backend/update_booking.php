<?php
header('Content-Type: application/json');

// DB connection
$conn = new mysqli("localhost", "root", "", "houselp_db");
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$data = json_decode(file_get_contents("php://input"), true);

// Prepare statement
$stmt = $conn->prepare("UPDATE bookings SET Homeowner_ID=?, Service_type=?, Booking_date=?, Status=?, Start_time=?, End_time=? WHERE ID=?");
$stmt->bind_param("ssssssi", 
    $data["Homeowner_ID"], 
    $data["Service_type"], 
    $data["Booking_date"], 
    $data["Status"], 
    $data["Start_time"], 
    $data["End_time"], 
    $data["ID"]
);

if ($stmt->execute()) {
    echo json_encode(["message" => "Booking updated successfully"]);
} else {
    echo json_encode(["error" => "Failed to update booking"]);
}

$conn->close();
?>
