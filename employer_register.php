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
