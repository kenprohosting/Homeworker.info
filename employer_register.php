<?php
require_once('db_connect.php');

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $country = $_POST['country'];
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
        // Store data in session
        $_SESSION['employer_reg_data'] = [
            'name' => $name,
            'country' => $country,
            'location' => $location,
            'residence' => $residence,
            'contact' => $contact,
            'gender' => $gender,
            'email' => $email,
            'address' => $address,
            'password_hash' => $password_hash
        ];
        header("Location: employer_register_payment.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <style>
      .country-dropdown {
        position: absolute;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 4px;
        max-height: 180px;
        overflow-y: auto;
        width: 100%;
        z-index: 9999;
        list-style: none;
        margin: 0;
        padding: 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      }
      .country-dropdown li {
        padding: 8px 12px;
        cursor: pointer;
      }
      .country-dropdown li:hover {
        background: #f0f0f0;
      }
      .form-container {
        max-width: 400px;
        margin: 40px auto 0 auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        padding: 18px 16px 0 16px;
        padding-bottom: 0 !important;
      }
      .form-container > *:last-child,
      .form-container p:last-of-type {
        margin-bottom: 0 !important;
        margin-top: 0 !important;
        padding-bottom: 0 !important;
      }
      .form-container p {
        margin-bottom: 0;
      }
    </style>
    <title>Employer Registration - Homeworker Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
</head>
<body style="background: #f4f8fb; font-family: 'Segoe UI', Arial, sans-serif; display: flex; flex-direction: column; min-height: 100vh;">

<div style="width:100%;text-align:center;margin:0;padding:0;">
    <img src="bghse.png" alt="Logo" style="height:48px;display:inline-block;margin:0 auto 0 auto;padding-top:8px;">
</div>
<div style="max-width:400px;margin:0 auto 0 auto;">
</div>

<div class="form-container" style="max-width: 360px; margin: 24px auto; background: #ffffff; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); padding: 24px; display: flex; flex-direction: column; gap: 16px;">
    <a href="index.php" style="color: #197b88; text-decoration: none; font-weight: 500; align-self: flex-start;">&larr; Back</a>
    <h2 style="text-align: center; color: #197b88; margin: 0; font-size: 1.5rem;">Register as Employer</h2>

    <?php
    if ($errors) foreach ($errors as $e) echo "<p style=\"background: #ffeaea; color: #c0392b; padding: 8px 12px; border-radius: 8px; margin: 0; text-align: center;\">$e</p>";
    if ($success) echo "<p style=\"background: #e6f4ea; color: #2e7d32; padding: 8px 12px; border-radius: 8px; margin: 0; text-align: center;\">$success</p>";
    ?>

    <form method="POST" style="display: flex; flex-direction: column; gap: 12px;">
        <input type="text" name="name" placeholder="Full Name" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <div style="position: relative;">
          <input type="text" id="countryInput" name="country" placeholder="Country" autocomplete="off" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
          <ul id="countryList" class="country-dropdown" style="position: absolute; background: #fff; border: 1px solid #ccc; border-radius: 4px; max-height: 180px; overflow-y: auto; width: 100%; z-index: 9999; list-style: none; margin: 0; padding: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.08);"></ul>
        </div>
        <input type="text" name="location" placeholder="county or province" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <select name="residence" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
            <option value="">Residence Type</option>
            <option value="urban">Urban</option>
            <option value="rural">Rural</option>
        </select>
        <input type="text" name="contact" placeholder="Phone Number" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <select name="gender" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
            <option value="">Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select>
        <input type="text" name="address" placeholder="Address (e.g. 123 West Street)" style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <input type="email" name="email" placeholder="Email" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <div style="position: relative;">
          <input type="password" name="password" id="password" placeholder="Password" required style="padding: 12px 36px 12px 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s; width: 100%; box-sizing: border-box;">
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

    if (input.length === 0) {
      countryList.style.display = "none";
      return;
    }

    const filtered = countries.filter(c =>
      c.name.toLowerCase().startsWith(input)
    );

    filtered.forEach(c => {
      const li = document.createElement("li");
      li.textContent = `${c.flag} ${c.name}`;
      li.addEventListener("click", () => {
        countryInput.value = c.name;
        countryList.innerHTML = "";
        countryList.style.display = "none";
      });
      countryList.appendChild(li);
    });

    countryList.style.display = filtered.length ? "block" : "none";
  });

  // Hide dropdown when clicking outside
  document.addEventListener("click", function (e) {
    if (!countryList.contains(e.target) && e.target !== countryInput) {
      countryList.style.display = "none";
    }
  });
</script>

<footer style="margin-top: auto; text-align: center; color: #888; padding: 16px 0;">
  <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
</footer>
<script>
function togglePassword(id, el) {
  var input = document.getElementById(id);
  if (input.type === "password") {
    input.type = "text";
    el.innerHTML = "&#128064;";
  } else {
    input.type = "password";
    el.innerHTML = "&#128065;";
  }
}
</script>

</body>
</html>
