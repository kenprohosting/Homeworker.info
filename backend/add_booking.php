<?php
include 'db.php';

// Get data from POST
$homeowner_id = $_POST['Homeowner_ID'];
$service_type = $_POST['Service_type'];
$booking_date = $_POST['Booking_date'];
$status = $_POST['Status'];
$start_time = $_POST['Start_time'];
$end_time = $_POST['End_time'];

$sql = "INSERT INTO bookings (Homeowner_ID, Service_type, Booking_date, Status, Start_time, End_time)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $homeowner_id, $service_type, $booking_date, $status, $start_time, $end_time);

if ($stmt->execute()) {
    echo json_encode(["message" => "Booking added successfully"]);
} else {
    echo json_encode(["message" => "Failed to add booking"]);
}

$stmt->close();
$conn->close();
?>
