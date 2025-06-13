<?php
include 'db.php';

$ID = $_POST['ID'];
$Date = $_POST['Date'];
$Amount = $_POST['Amount'];
$Description = $_POST['Description'];
$Employer_ID = $_POST['Employer_ID'];

$stmt = $conn->prepare("UPDATE payment SET Date=?, Amount=?, Description=?, Employer_ID=? WHERE ID=?");
$stmt->bind_param("sisii", $Date, $Amount, $Description, $Employer_ID, $ID);

if ($stmt->execute()) {
    echo "Payment updated successfully.";
} else {
    echo "Error: " . $stmt->error;
}
?>
