<?php
require_once('db_connect.php');

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];
    $contact = $_POST['contact'];
    $location = $_POST['location'];
    $skills = $_POST['skills'];
    $education = $_POST['education'];
    $referee = $_POST['referee'];
    $language = $_POST['language'];
    $residence = $_POST['residence'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Check if email already exists
    $check = $conn->prepare("SELECT * FROM employee WHERE email = ?");
    $check->execute([$email]);
    if ($check->rowCount() > 0) {
        $errors[] = "Email already exists.";
    }

    // Insert data
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO employee (Name, Gender, Age, Contact, Location, Skills, Education_level, Social_referee, Language, email, password_hash, residence_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$name, $gender, $age, $contact, $location, $skills, $education, $referee, $language, $email, $password_hash, $residence]);

        if ($result) {
            $success = "Registration successful. Please <a href='employee_login.php'>login</a>.";
        } else {
            $errors[] = "Failed to register. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="form-container">
    <h2>Register as Employee</h2>

    <?php
    if ($errors) foreach ($errors as $e) echo "<p class='error'>$e</p>";
    if ($success) echo "<p class='success'>$success</p>";
    ?>

    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <select name="gender" required>
            <option value="">Select Gender</option>
            <option>Male</option>
            <option>Female</option>
        </select>
        <input type="number" name="age" placeholder="Age" required>
        <input type="text" name="contact" placeholder="Phone Number" required>
        <input type="text" name="location" placeholder="Location" required>
        <input type="text" name="skills" placeholder="Skills (e.g. Cleaning)" required>
        <input type="text" name="education" placeholder="Education Level" required>
        <input type="text" name="referee" placeholder="Social Referee Name" required>
       <select name="language" required>
       <option value="">-- Select Language --</option>
       <option value="English">English</option>
       <option value="Kiswahili">Kiswahili</option>
       <option value="Both">Both</option>
       </select><br><br>

        <select name="residence" required>
            <option value="">Residence Type</option>
            <option value="urban">Urban</option>
            <option value="rural">Rural</option>
        </select>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="btn">Register</button>
    </form>

    <p style="text-align:center; margin-top:15px;">
        Already have an account? <a href="employee_login.php">Login</a>
    </p>
</div>

</body>
</html>
