<?php
header('Content-Type: application/json');

// Connect to database
$conn = new mysqli("localhost", "root", "", "houselp_db");
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$data = json_decode(file_get_contents("php://input"), true);

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO payment (Date, Amount, Description, Employer_ID) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sdsi", 
    $data["Date"], 
    $data["Amount"], 
    $data["Description"], 
    $data["Employer_ID"]
);

if ($stmt->execute()) {
    echo json_encode(["message" => "Payment added successfully"]);
} else {
    echo json_encode(["error" => "Failed to add payment"]);
}

$conn->close();
?>
