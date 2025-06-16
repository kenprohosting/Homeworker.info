<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$ID = $data['ID'];
$Name = $data['Name'];
$Location = $data['Location'];
$Contact = $data['Contact'];

$sql = "UPDATE employer SET Name = ?, Location = ?, Contact = ? WHERE ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $Name, $Location, $Contact, $ID);

if ($stmt->execute()) {
    echo json_encode(["message" => "Employer updated successfully"]);
} else {
    echo json_encode(["message" => "Update failed"]);
}

$conn->close();
?>
