<?php
include 'db.php';

$Rating = $_POST['Rating'];
$Comment = $_POST['Comment'];
$Date = $_POST['Date']; // Format: YYYY-MM-DD

$stmt = $conn->prepare("INSERT INTO review_table (Rating, Comment, Date) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $Rating, $Comment, $Date);

if ($stmt->execute()) {
    echo "Review added successfully.";
} else {
    echo "Error: " . $stmt->error;
}
?>
