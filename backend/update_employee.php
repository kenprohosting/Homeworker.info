<?php
include 'db.php';

$ID = $_POST['ID'];
$Name = $_POST['Name'];
$Gender = $_POST['Gender'];
$Age = $_POST['Age'];
$Contact = $_POST['Contact'];
$Location = $_POST['Location'];
$Skills = $_POST['Skills'];

$sql = "UPDATE employee SET Name=?, Gender=?, Age=?, Contact=?, Location=?, Skills=? WHERE ID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssisssi", $Name, $Gender, $Age, $Contact, $Location, $Skills, $ID);

if ($stmt->execute()) {
    echo json_encode(["message" => "Employee updated successfully"]);
} else {
    echo json_encode(["message" => "Update failed"]);
}
?>
