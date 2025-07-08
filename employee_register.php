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
        <input type="number" name="age" placeholder="Age" min="18" max="60" required>
        <input type="text" name="contact" placeholder="Phone Number" required>
        <select name="location" required>
          <option value="" disabled selected>Select your county</option>
          <option value="baringo">Baringo</option>
          <option value="bomet">Bomet</option>
          <option value="bungoma">Bungoma</option>
          <option value="busia">Busia</option>
          <option value="elgeyo-marakwet">Elgeyo-Marakwet</option>
          <option value="embu">Embu</option>
          <option value="garissa">Garissa</option>
          <option value="homa-bay">Homa Bay</option>
          <option value="isiolo">Isiolo</option>
          <option value="kajiado">Kajiado</option>
          <option value="kakamega">Kakamega</option>
          <option value="kericho">Kericho</option>
          <option value="kiambu">Kiambu</option>
          <option value="kilifi">Kilifi</option>
          <option value="kirinyaga">Kirinyaga</option>
          <option value="kisii">Kisii</option>
          <option value="kisumu">Kisumu</option>
          <option value="kitui">Kitui</option>
          <option value="kwale">Kwale</option>
          <option value="laikipia">Laikipia</option>
          <option value="lamu">Lamu</option>
          <option value="machakos">Machakos</option>
          <option value="makueni">Makueni</option>
          <option value="mandera">Mandera</option>
          <option value="meru">Meru</option>
          <option value="migori">Migori</option>
          <option value="marsabit">Marsabit</option>
          <option value="mombasa">Mombasa</option>
          <option value="murang'a">Murang'a</option>
          <option value="nairobi">Nairobi</option>
          <option value="nakuru">Nakuru</option>
          <option value="nandi">Nandi</option>
          <option value="narok">Narok</option>
          <option value="nyamira">Nyamira</option>
          <option value="nyandarua">Nyandarua</option>
          <option value="nyeri">Nyeri</option>
          <option value="samburu">Samburu</option>
          <option value="siaya">Siaya</option>
          <option value="taita-taveta">Taita-Taveta</option>
          <option value="tana-river">Tana River</option>
          <option value="tharaka-nithi">Tharaka-Nithi</option>
          <option value="trans-nzoia">Trans Nzoia</option>
          <option value="turkana">Turkana</option>
          <option value="uasin-gishu">Uasin Gishu</option>
          <option value="vihiga">Vihiga</option>
          <option value="wajir">Wajir</option>
          <option value="west-pokot">West Pokot</option>
        </select>
        <input type="text" name="skills" placeholder="Skills (e.g. Baby Sitting)" required>
        <select name="education" required>
            <option value="">--Select Education Level--</option>
            <option>Primary</option>
            <option>Secondary</option>
            <option>Tertiary</option>
        <input type="text" name="referee" placeholder="Social Referee Contact" required>
        <input type="text" name="referee" placeholder="Professional Referee Contact" required>
       <select name="language" required>
       <option value="">-- Select Language --</option>
       <option value="English">English</option>
       <option value="Kiswahili">Kiswahili</option>
       <option value="Both">Both</option>
       </select><br>
        <select name="residence" required>
            <option value="">Residence Type</option>
            <option value="urban">Urban</option>
            <option value="rural">Rural</option>
        </select>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="btn">Register</button>
        <script>
    document.getElementById("age").addEventListener("input", function () {
      const age = parseInt(this.value, 10);
      if (age <= 18 || age >= 60) {
        this.setCustomValidity("Age must be more than 18 and less than 60.");
      } else {
        this.setCustomValidity("");
      }
    });
  </script>
    </form>

    <p style="text-align:center; margin-top:15px;">
        Already have an account? <a href="employee_login.php">Login</a>
    </p>
</div>

</body>
</html>
