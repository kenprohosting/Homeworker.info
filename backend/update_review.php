<?php
include 'db.php';

$ID = $_POST['ID'];
$Rating = $_POST['Rating'];
$Comment = $_POST['Comment'];
$Date = $_POST['Date'];

$stmt = $conn->prepare("UPDATE review_table SET Rating=?, Comment=?, Date=? WHERE ID=?");
$stmt->bind_param("issi", $Rating, $Comment, $Date, $ID);

if ($stmt->execute()) {
    echo "Review updated successfully.";
} else {
    echo "Error: " . $stmt->error;
}
?>
