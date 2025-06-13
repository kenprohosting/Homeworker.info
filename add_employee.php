<?php
include 'db.php'; // Ensure this file connects to your MySQL correctly

// Check if data was sent using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST['Name'] ?? '';
    $gender   = $_POST['Gender'] ?? '';
    $age      = $_POST['Age'] ?? 0;
    $contact  = $_POST['Contact'] ?? '';
    $location = $_POST['Location'] ?? '';
    $skills   = $_POST['Skills'] ?? '';

    // Basic check
    if (empty($name) || empty($gender) || empty($age) || empty($contact) || empty($location) || empty($skills)) {
        echo "Error: All fields are required.";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO employee (Name, Gender, Age, Contact, Location, Skills) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisss", $name, $gender, $age, $contact, $location, $skills);

    if ($stmt->execute()) {
        echo "Employee added successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Please send data using POST method.";
}
?>
