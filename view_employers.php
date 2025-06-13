<?php
include 'db.php';

$sql = "SELECT * FROM employer";
$result = $conn->query($sql);

$employers = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employers[] = $row;
    }
    echo json_encode($employers);
} else {
    echo json_encode(["message" => "No employers found"]);
}
?>
