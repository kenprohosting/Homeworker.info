<?php
require_once('db_connect.php');

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $residence = $_POST['residence'];
    $contact = $_POST['contact'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $password = $_POST['password'];
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Check for existing email
    $check = $conn->prepare("SELECT * FROM employer WHERE email = ?");
    $check->execute([$email]);
    if ($check->rowCount() > 0) {
        $errors[] = "Email is already in use.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO employer (Name, Location, Residence_type, Contact, Gender, email, password_hash, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$name, $location, $residence, $contact, $gender, $email, $password_hash, $address]);

        if ($result) {
            $success = "Registration successful. Please <a href='employer_login.php'>login</a>.";
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employer Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="form-container">
    <h2>Register as Employer</h2>

    <?php
    if ($errors) foreach ($errors as $e) echo "<p class='error'>$e</p>";
    if ($success) echo "<p class='success' style='text-align:center;'>$success</p>";
    ?>

    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="text" name="location" placeholder="Location" required>
        <select name="residence" required>
            <option value="">Residence Type</option>
            <option value="urban">Urban</option>
            <option value="rural">Rural</option>
        </select>
        <input type="text" name="contact" placeholder="Phone Number" required>
        <select name="gender" required>
            <option value="">Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select>
        <input type="text" name="address" placeholder="Address (e.g. 123 West Street)">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="btn">Register</button>
    </form>

    <p style="text-align:center; margin-top:15px;">
        Already have an account? <a href="employer_login.php">Login</a>
    </p>
</div>

</body>
</html>
