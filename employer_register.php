<?php
session_start();
require_once('db_connect.php');

$errors = [];
$success = '';
$name = $country = $location = $residence = $contact = $gender = $email = $address = $password = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // CSRF check
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $errors[] = "Invalid form submission.";
  }

  // Fetch & sanitize inputs
  $name = trim($_POST['name'] ?? '');
  $country = trim($_POST['country'] ?? '');
  $location = trim($_POST['location'] ?? '');
  $residence = trim($_POST['residence'] ?? '');
  $contact = trim($_POST['contact'] ?? '');
  $gender = trim($_POST['gender'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $password = $_POST['password'] ?? '';

  // Validation
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
  }
  if (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters.";
  }
  if (!preg_match('/^\+?[0-9]{7,15}$/', $contact)) {
    $errors[] = "Invalid phone number.";
  }

  // Email check
  $check = $conn->prepare("SELECT * FROM employer WHERE email = ?");
  $check->execute([$email]);
  if ($check->rowCount() > 0) {
    $errors[] = "Email is already in use.";
  }

  if (empty($errors)) {
    $_SESSION['employer_reg_data'] = [
      'name' => $name,
      'country' => $country,
      'location' => $location,
      'residence' => $residence,
      'contact' => $contact,
      'gender' => $gender,
      'email' => $email,
      'address' => $address,
      'password' => $password // Save raw for now; hash after payment
    ];
    header("Location: employer_register_payment.php");
    exit();
  }
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employer Registration</title>
  <style>
    .error { background: #ffeaea; color: #c0392b; padding: 8px; margin-bottom: 10px; border-radius: 5px; text-align: center; }
    .form-container { max-width: 400px; margin: auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    input, select { width: 100%; padding: 10px; margin-bottom: 12px; border-radius: 6px; border: 1px solid #ccc; }
    button { background: #197b88; color: #fff; border: none; padding: 10px; border-radius: 6px; cursor: pointer; }
    .country-dropdown { position: absolute; background: #fff; border: 1px solid #ccc; max-height: 180px; overflow-y: auto; z-index: 1000; width: 100%; border-radius: 4px; }
    .country-dropdown li { padding: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; }
    .country-dropdown li:hover { background: #f0f0f0; }
  </style>
</head>
<body style="background: #f4f8fb; font-family: sans-serif;">
<div class="form-container">
  <h2 style="text-align:center;">Register as Employer</h2>
  <?php foreach ($errors as $error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endforeach; ?>
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="text" name="name" placeholder="Full Name" value="<?= htmlspecialchars($name) ?>" required>
    <select name="gender" required>
      <option value="">Select Gender</option>
      <option value="male" <?= $gender === 'male' ? 'selected' : '' ?>>Male</option>
      <option value="female" <?= $gender === 'female' ? 'selected' : '' ?>>Female</option>
      <option value="other" <?= $gender === 'other' ? 'selected' : '' ?>>Other</option>
    </select>
    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>" required>
    <div style="position: relative;">
      <input type="text" id="countryInput" name="country" placeholder="Country" value="<?= htmlspecialchars($country) ?>" required autocomplete="off">
      <ul id="countryList" class="country-dropdown"></ul>
    </div>
    <input type="text" name="location" placeholder="County, Province or State" value="<?= htmlspecialchars($location) ?>" required>
    <input type="text" name="contact" id="phoneInput" placeholder="Phone Number" value="<?= htmlspecialchars($contact) ?>" required>
    <input type="text" name="address" placeholder="Address" value="<?= htmlspecialchars($address) ?>">
    <select name="residence" required>
      <option value="">Residence Type</option>
      <option value="urban" <?= $residence === 'urban' ? 'selected' : '' ?>>Urban</option>
      <option value="rural" <?= $residence === 'rural' ? 'selected' : '' ?>>Rural</option>
    </select>
    <input type="password" name="password" placeholder="Password" required>
    <div>
      <label><input type="checkbox" required> I agree to the <a href="terms_and_conditions.php">Terms and Conditions</a></label>
    </div>
    <button type="submit">Pay to Complete Registration</button>
  </form>
</div>

<script>
const countryInput = document.getElementById("countryInput");
const countryList = document.getElementById("countryList");
const phoneInput = document.getElementById("phoneInput");
let countries = [];

if (localStorage.getItem("countryCache")) {
  countries = JSON.parse(localStorage.getItem("countryCache"));
} else {
  fetch("https://restcountries.com/v3.1/all")
    .then(res => res.json())
    .then(data => {
      countries = data.map(c => {
        const name = c.name.common;
        const flag = c.flag || "";
        const dialCode = c.idd?.root && c.idd.suffixes ? c.idd.root + c.idd.suffixes[0] : "";
        return { name, flag, dialCode };
      }).filter(c => c.dialCode);
      localStorage.setItem("countryCache", JSON.stringify(countries));
    });
}

countryInput.addEventListener("input", function () {
  const input = this.value.toLowerCase();
  countryList.innerHTML = "";

  if (!input) {
    countryList.style.display = "none";
    return;
  }

  const filtered = countries.filter(c => c.name.toLowerCase().startsWith(input));

  filtered.forEach(c => {
    const li = document.createElement("li");
    li.innerHTML = `<span>${c.flag}</span> <span>${c.name}</span>`;
    li.addEventListener("click", () => {
      countryInput.value = c.name;
      phoneInput.value = c.dialCode;
      countryList.innerHTML = "";
      countryList.style.display = "none";
    });
    countryList.appendChild(li);
  });

  countryList.style.display = filtered.length ? "block" : "none";
});

document.addEventListener("click", function (e) {
  if (!countryList.contains(e.target) && e.target !== countryInput) {
    countryList.style.display = "none";
  }
});

countryInput.addEventListener("blur", function () {
  const match = countries.find(c => c.name.toLowerCase() === countryInput.value.toLowerCase());
  if (match) {
    phoneInput.value = match.dialCode;
  }
});
</script>
</body>
</html>
