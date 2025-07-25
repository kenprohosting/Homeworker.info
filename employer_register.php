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

    <form method="POST" style="display: flex; flex-direction: column; gap: 12px;">
        <input type="text" name="name" placeholder="Full Name" value="<?php echo htmlspecialchars($name); ?>" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <div style="position: relative;">
          <input type="text" id="countryInput" name="country" placeholder="Country" value="<?php echo htmlspecialchars($country); ?>" autocomplete="off" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
          <ul id="countryList" class="country-dropdown" style="position: absolute; background: #fff; border: 1px solid #ccc; border-radius: 4px; max-height: 180px; overflow-y: auto; width: 100%; z-index: 9999; list-style: none; margin: 0; padding: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.08);"></ul>
        </div>
        <input type="text" name="location" placeholder="county or province" value="<?php echo htmlspecialchars($location); ?>" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <select name="residence" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
            <option value="">Residence Type</option>
            <option value="urban" <?php if ($residence == 'urban') echo 'selected'; ?>>Urban</option>
            <option value="rural" <?php if ($residence == 'rural') echo 'selected'; ?>>Rural</option>
        </select>
        <input type="text" name="contact" placeholder="Phone Number" value="<?php echo htmlspecialchars($contact); ?>" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <select name="gender" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
            <option value="">Gender</option>
            <option value="male" <?php if ($gender == 'male') echo 'selected'; ?>>Male</option>
            <option value="female" <?php if ($gender == 'female') echo 'selected'; ?>>Female</option>
            <option value="other" <?php if ($gender == 'other') echo 'selected'; ?>>Other</option>
        </select>
        <input type="text" name="address" placeholder="Address (e.g. 123 West Street)" value="<?php echo htmlspecialchars($address); ?>" style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <div style="position: relative;">
          <input type="password" name="password" id="password" placeholder="Password" value="<?php echo htmlspecialchars($password); ?>" required style="padding: 12px 36px 12px 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s; width: 100%; box-sizing: border-box;">
          <span onclick="togglePassword('password', this)" style="position: absolute; top: 50%; right: 12px; transform: translateY(-50%); cursor: pointer; font-size: 1.2em;">&#128065;</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; color: #333;">
    <input type="checkbox" id="terms" name="terms" required style="cursor: pointer;">
    <label for="terms">I agree to the <a href="terms_and_conditions.php" target="_blank" style="color: #197b88; text-decoration: underline;">Terms and Conditions</a></label>
</div>
<button type="submit" style="background: linear-gradient(135deg, #197b88, #1ec8c8); color: #fff; border: none; border-radius: 8px; padding: 12px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: background 0.3s;">Pay to Complete Registration</button>
    </form>

    <p style="text-align: center; margin: 0; font-size: 0.9rem;">
      Already have an account? <a href="employer_login.php" style="color: #197b88; text-decoration: none;">Login</a>
    </p>
  </div>
  <script>
    const countryInput = document.getElementById("countryInput");
    const countryList = document.getElementById("countryList");
    const countries = [
      { name: "Afghanistan", flag: "🇦🇫" },
      { name: "Albania", flag: "🇦🇱" },
      { name: "Algeria", flag: "🇩🇿" },
      { name: "Andorra", flag: "🇦🇩" },
      { name: "Angola", flag: "🇦🇴" },
      { name: "Antigua and Barbuda", flag: "🇦🇬" },
      { name: "Argentina", flag: "🇦🇷" },
      { name: "Armenia", flag: "🇦🇲" },
      { name: "Australia", flag: "🇦🇺" },
      { name: "Austria", flag: "🇦🇹" },
      { name: "Azerbaijan", flag: "🇦🇿" },
      { name: "Bahamas", flag: "🇧🇸" },
      { name: "Bahrain", flag: "🇧🇭" },
      { name: "Bangladesh", flag: "🇧🇩" },
      { name: "Barbados", flag: "🇧🇧" },
      { name: "Belarus", flag: "🇧🇾" },
      { name: "Belgium", flag: "🇧🇪" },
      { name: "Belize", flag: "🇧🇿" },
      { name: "Benin", flag: "🇧🇯" },
      { name: "Bhutan", flag: "🇧🇹" },
      { name: "Bolivia", flag: "🇧🇴" },
      { name: "Bosnia and Herzegovina", flag: "🇧🇦" },
      { name: "Botswana", flag: "🇧🇼" },
      { name: "Brazil", flag: "🇧🇷" },
      { name: "Brunei", flag: "🇧🇳" },
      { name: "Bulgaria", flag: "🇧🇬" },
      { name: "Burkina Faso", flag: "🇧🇫" },
      { name: "Burundi", flag: "🇧🇮" },
      { name: "Cabo Verde", flag: "🇨🇻" },
      { name: "Cambodia", flag: "🇰🇭" },
      { name: "Cameroon", flag: "🇨🇲" },
      { name: "Canada", flag: "🇨🇦" },
      { name: "Central African Republic", flag: "🇨🇫" },
      { name: "Chad", flag: "🇹🇩" },
      { name: "Chile", flag: "🇨🇱" },
      { name: "China", flag: "🇨🇳" },
      { name: "Colombia", flag: "🇨🇴" },
      { name: "Comoros", flag: "🇰🇲" },
      { name: "Congo (Brazzaville)", flag: "🇨🇬" },
      { name: "Congo (Kinshasa)", flag: "🇨🇩" },
      { name: "Costa Rica", flag: "🇨🇷" },
      { name: "Croatia", flag: "🇭🇷" },
      { name: "Cuba", flag: "🇨🇺" },
      { name: "Cyprus", flag: "🇨🇾" },
      { name: "Czech Republic", flag: "🇨🇿" },
      { name: "Denmark", flag: "🇩🇰" },
      { name: "Djibouti", flag: "🇩🇯" },
      { name: "Dominica", flag: "🇩🇲" },
      { name: "Dominican Republic", flag: "🇩🇴" },
      { name: "Ecuador", flag: "🇪🇨" },
      { name: "Egypt", flag: "🇪🇬" },
      { name: "El Salvador", flag: "🇸🇻" },
      { name: "Equatorial Guinea", flag: "🇬🇶" },
      { name: "Eritrea", flag: "🇪🇷" },
      { name: "Estonia", flag: "🇪🇪" },
      { name: "Eswatini", flag: "🇸🇿" },
      { name: "Ethiopia", flag: "🇪🇹" },
      { name: "Fiji", flag: "🇫🇯" },
      { name: "Finland", flag: "🇫🇮" },
      { name: "France", flag: "🇫🇷" },
      { name: "Gabon", flag: "🇬🇦" },
      { name: "Gambia", flag: "🇬🇲" },
      { name: "Georgia", flag: "🇬🇪" },
      { name: "Germany", flag: "🇩🇪" },
      { name: "Ghana", flag: "🇬🇭" },
      { name: "Greece", flag: "🇬🇷" },
      { name: "Grenada", flag: "🇬🇩" },
      { name: "Guatemala", flag: "🇬🇹" },
      { name: "Guinea", flag: "🇬🇳" },
      { name: "Guinea-Bissau", flag: "🇬🇼" },
      { name: "Guyana", flag: "🇬🇾" },
      { name: "Haiti", flag: "🇭🇹" },
      { name: "Honduras", flag: "🇭🇳" },
      { name: "Hungary", flag: "🇭🇺" },
      { name: "Iceland", flag: "🇮🇸" },
      { name: "India", flag: "🇮🇳" },
      { name: "Indonesia", flag: "🇮🇩" },
      { name: "Iran", flag: "🇮🇷" },
      { name: "Iraq", flag: "🇮🇶" },
      { name: "Ireland", flag: "🇮🇪" },
      { name: "Israel", flag: "🇮🇱" },
      { name: "Italy", flag: "🇮🇹" },
      { name: "Jamaica", flag: "🇯🇲" },
      { name: "Japan", flag: "🇯🇵" },
      { name: "Jordan", flag: "🇯🇴" },
      { name: "Kazakhstan", flag: "🇰🇿" },
      { name: "Kenya", flag: "🇰🇪" },
      { name: "Kiribati", flag: "🇰🇮" },
      { name: "Kuwait", flag: "🇰🇼" },
      { name: "Kyrgyzstan", flag: "🇰🇬" },
      { name: "Laos", flag: "🇱🇦" },
      { name: "Latvia", flag: "🇱🇻" },
      { name: "Lebanon", flag: "🇱🇧" },
      { name: "Lesotho", flag: "🇱🇸" },
      { name: "Liberia", flag: "🇱🇷" },
      { name: "Libya", flag: "🇱🇾" },
      { name: "Liechtenstein", flag: "🇱🇮" },
      { name: "Lithuania", flag: "🇱🇹" },
      { name: "Luxembourg", flag: "🇱🇺" },
      { name: "Madagascar", flag: "🇲🇬" },
      { name: "Malawi", flag: "🇲🇼" },
      { name: "Malaysia", flag: "🇲🇾" },
      { name: "Maldives", flag: "🇲🇻" },
      { name: "Mali", flag: "🇲🇱" },
      { name: "Malta", flag: "🇲🇹" },
      { name: "Marshall Islands", flag: "🇲🇭" },
      { name: "Mauritania", flag: "🇲🇷" },
      { name: "Mauritius", flag: "🇲🇺" },
      { name: "Mexico", flag: "🇲🇽" },
      { name: "Micronesia", flag: "🇫🇲" },
      { name: "Moldova", flag: "🇲🇩" },
      { name: "Monaco", flag: "🇲🇨" },
      { name: "Mongolia", flag: "🇲🇳" },
      { name: "Montenegro", flag: "🇲🇪" },
      { name: "Morocco", flag: "🇲🇦" },
      { name: "Mozambique", flag: "🇲🇿" },
      { name: "Myanmar", flag: "🇲🇲" },
      { name: "Namibia", flag: "🇳🇦" },
      { name: "Nauru", flag: "🇳🇷" },
      { name: "Nepal", flag: "🇳🇵" },
      { name: "Netherlands", flag: "🇳🇱" },
      { name: "New Zealand", flag: "🇳🇿" },
      { name: "Nicaragua", flag: "🇳🇮" },
      { name: "Niger", flag: "🇳🇪" },
      { name: "Nigeria", flag: "🇳🇬" },
      { name: "North Korea", flag: "🇰🇵" },
      { name: "North Macedonia", flag: "🇲🇰" },
      { name: "Norway", flag: "🇳🇴" },
      { name: "Oman", flag: "🇴🇲" },
      { name: "Pakistan", flag: "🇵🇰" },
      { name: "Palau", flag: "🇵🇼" },
      { name: "Panama", flag: "🇵🇦" },
      { name: "Papua New Guinea", flag: "🇵🇬" },
      { name: "Paraguay", flag: "🇵🇾" },
      { name: "Peru", flag: "🇵🇪" },
      { name: "Philippines", flag: "🇵🇭" },
      { name: "Poland", flag: "🇵🇱" },
      { name: "Portugal", flag: "🇵🇹" },
      { name: "Qatar", flag: "🇶🇦" },
      { name: "Romania", flag: "🇷🇴" },
      { name: "Russia", flag: "🇷🇺" },
      { name: "Rwanda", flag: "🇷🇼" },
      { name: "Saint Kitts and Nevis", flag: "🇰🇳" },
      { name: "Saint Lucia", flag: "🇱🇨" },
      { name: "Saint Vincent and the Grenadines", flag: "🇻🇨" },
      { name: "Samoa", flag: "🇼🇸" },
      { name: "San Marino", flag: "🇸🇲" },
      { name: "Sao Tome and Principe", flag: "🇸🇹" },
      { name: "Saudi Arabia", flag: "🇸🇦" },
      { name: "Senegal", flag: "🇸🇳" },
      { name: "Serbia", flag: "🇷🇸" },
      { name: "Seychelles", flag: "🇸🇨" },
      { name: "Sierra Leone", flag: "🇸🇱" },
      { name: "Singapore", flag: "🇸🇬" },
      { name: "Slovakia", flag: "🇸🇰" },
      { name: "Slovenia", flag: "🇸🇮" },
      { name: "Solomon Islands", flag: "🇸🇧" },
      { name: "Somalia", flag: "🇸🇴" },
      { name: "South Africa", flag: "🇿🇦" },
      { name: "South Korea", flag: "🇰🇷" },
      { name: "South Sudan", flag: "🇸🇸" },
      { name: "Spain", flag: "🇪🇸" },
      { name: "Sri Lanka", flag: "🇱🇰" },
      { name: "Sudan", flag: "🇸🇩" },
      { name: "Suriname", flag: "🇸🇷" },
      { name: "Sweden", flag: "🇸🇪" },
      { name: "Switzerland", flag: "🇨🇭" },
      { name: "Syria", flag: "🇸🇾" },
      { name: "Taiwan", flag: "🇹🇼" },
      { name: "Tajikistan", flag: "🇹🇯" },
      { name: "Tanzania", flag: "🇹🇿" },
      { name: "Thailand", flag: "🇹🇭" },
      { name: "Timor-Leste", flag: "🇹🇱" },
      { name: "Togo", flag: "🇹🇬" },
      { name: "Tonga", flag: "🇹🇴" },
      { name: "Trinidad and Tobago", flag: "🇹🇹" },
      { name: "Tunisia", flag: "🇹🇳" },
      { name: "Turkey", flag: "🇹🇷" },
      { name: "Turkmenistan", flag: "🇹🇲" },
      { name: "Tuvalu", flag: "🇹🇻" },
      { name: "Uganda", flag: "🇺🇬" },
      { name: "Ukraine", flag: "🇺🇦" },
      { name: "United Arab Emirates", flag: "🇦🇪" },
      { name: "United Kingdom", flag: "🇬🇧" },
      { name: "United States", flag: "🇺🇸" },
      { name: "Uruguay", flag: "🇺🇾" },
      { name: "Uzbekistan", flag: "🇺🇿" },
      { name: "Vanuatu", flag: "🇻🇺" },
      { name: "Vatican City", flag: "🇻🇦" },
      { name: "Venezuela", flag: "🇻🇪" },
      { name: "Vietnam", flag: "🇻🇳" },
      { name: "Yemen", flag: "🇾🇪" },
      { name: "Zambia", flag: "🇿🇲" },
      { name: "Zimbabwe", flag: "🇿🇼" }
    ];

    countryInput.addEventListener("input", function () {
      const input = this.value.toLowerCase();
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
