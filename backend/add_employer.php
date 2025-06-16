<?php
include 'db.php';

// Collect data from POST request
$Name = $_POST['Name'];
$Location = $_POST['Location'];
$Contact = $_POST['Contact'];

// Prepare SQL
$sql = "INSERT INTO employer (Name, Location, Contact) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $Name, $Location, $Contact);

// Execute and return response
if ($stmt->execute()) {
    echo json_encode(["message" => "Employer added successfully"]);
} else {
    echo json_encode(["message" => "Failed to add employer"]);
}
?>
