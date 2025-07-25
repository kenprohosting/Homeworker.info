<?php
session_start();
require_once('db_connect.php');

$errors = [];
$success = '';
$name = '';
$country = '';
$location = '';
$residence = '';
$contact = '';
$gender = '';
$email = '';
$address = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = $_POST['name'] ?? '';
  $country = $_POST['country'] ?? '';
  $location = $_POST['location'] ?? '';
  $residence = $_POST['residence'] ?? '';
  $contact = $_POST['contact'] ?? '';
  $gender = $_POST['gender'] ?? '';
  $email = $_POST['email'] ?? '';
  $address = $_POST['address'] ?? '';
  $password = $_POST['password'] ?? '';
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
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
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
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
      padding: 18px 16px 0 16px;
      padding-bottom: 0 !important;
    }

    .form-container>*:last-child,
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

<body
  style="background: #f4f8fb; font-family: 'Segoe UI', Arial, sans-serif; display: flex; flex-direction: column; min-height: 100vh;">

  <div style="width:100%;text-align:center;margin:0;padding:0;">
    <img src="bghse.png" alt="Logo" style="height:48px;display:inline-block;margin:0 auto 0 auto;padding-top:8px;">
  </div>
  <div style="max-width:400px;margin:0 auto 0 auto;">
  </div>

  <div class="form-container"
    style="max-width: 360px; margin: 24px auto; background: #ffffff; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); padding: 24px; display: flex; flex-direction: column; gap: 16px;">
    <a href="index.php" style="color: #197b88; text-decoration: none; font-weight: 500; align-self: flex-start;">&larr;
      Back</a>
    <h2 style="text-align: center; color: #197b88; margin: 0; font-size: 1.5rem;">Register as Employer</h2>
    <h3 style="text-align: center; color: #197b88; margin: 0; font-size: 1.2rem;">One-time subscription: $1</h3>

    <?php
    if ($errors)
      foreach ($errors as $e)
        echo "<p style=\"background: #ffeaea; color: #c0392b; padding: 8px 12px; border-radius: 8px; margin: 0; text-align: center;\">$e</p>";
    if ($success)
      echo "<p style=\"background: #e6f4ea; color: #2e7d32; padding: 8px 12px; border-radius: 8px; margin: 0; text-align: center;\">$success</p>";
    ?>

    <form method="POST" style="display: flex; flex-direction: column; gap: 12px;">
      <input type="text" name="name" placeholder="Full Name" value="<?php echo htmlspecialchars($name); ?>" required
        style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
      <select name="gender" required
        style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <option value="">Gender</option>
        <option value="male" <?php if ($gender == 'male')
          echo 'selected'; ?>>Male</option>
        <option value="female" <?php if ($gender == 'female')
          echo 'selected'; ?>>Female</option>
        <option value="other" <?php if ($gender == 'other')
          echo 'selected'; ?>>Other</option>
      </select>
      <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required
        style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
      <div style="position: relative;">
        <input type="text" id="countryInput" name="country" placeholder="Country"
          value="<?php echo htmlspecialchars($country); ?>" autocomplete="off" required
          style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <ul id="countryList" class="country-dropdown"
          style="position: absolute; background: #fff; border: 1px solid #ccc; border-radius: 4px; max-height: 180px; overflow-y: auto; width: 100%; z-index: 9999; list-style: none; margin: 0; padding: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        </ul>
      </div>
      <input type="text" name="location" placeholder="County,province or state"
        value="<?php echo htmlspecialchars($location); ?>" required
        style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
      <input type="text" id="contactInput" name="contact" placeholder="Phone Number" value="<?php echo htmlspecialchars($contact); ?>"
        required
        style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">


      <input type="text" name="address" placeholder="Address (e.g. 123 West Street)"
        value="<?php echo htmlspecialchars($address); ?>"
        style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
      <select name="residence" required
        style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
        <option value="">Residence Type</option>
        <option value="urban" <?php if ($residence == 'urban')
          echo 'selected'; ?>>Urban</option>
        <option value="rural" <?php if ($residence == 'rural')
          echo 'selected'; ?>>Rural</option>
      </select>




      <div style="position: relative;">
        <input type="password" name="password" id="password" placeholder="Password"
          value="<?php echo htmlspecialchars($password); ?>" required
          style="padding: 12px 36px 12px 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s; width: 100%; box-sizing: border-box;">
        <span onclick="togglePassword('password', this)"
          style="position: absolute; top: 50%; right: 12px; transform: translateY(-50%); cursor: pointer; font-size: 1.2em;">&#128065;</span>
      </div>
      <div style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; color: #333;">
        <input type="checkbox" id="terms" name="terms" required style="cursor: pointer;">
        <label for="terms">I agree to the <a href="terms_and_conditions.php" target="_blank"
            style="color: #197b88; text-decoration: underline;">Terms and Conditions</a></label>
      </div>
      <button type="submit"
        style="background: linear-gradient(135deg, #197b88, #1ec8c8); color: #fff; border: none; border-radius: 8px; padding: 12px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: background 0.3s;">Pay
        to Complete Registration</button>
    </form>

    <p style="text-align: center; margin: 0; font-size: 0.9rem;">
      Already have an account? <a href="employer_login.php" style="color: #197b88; text-decoration: none;">Login</a>
    </p>
  </div>
  <script>
    const countryInput = document.getElementById("countryInput");
    const countryList = document.getElementById("countryList");
    const countries = [
      { name: "Afghanistan", flag: "🇦🇫", code: "+93" },
      { name: "Albania", flag: "🇦🇱", code: "+355" },
      { name: "Algeria", flag: "🇩🇿", code: "+213" },
      { name: "Andorra", flag: "🇦🇩", code: "+376" },
      { name: "Angola", flag: "🇦🇴", code: "+244" },
      { name: "Antigua and Barbuda", flag: "🇦🇬", code: "+1" },
      { name: "Argentina", flag: "🇦🇷", code: "+54" },
      { name: "Armenia", flag: "🇦🇲", code: "+374" },
      { name: "Australia", flag: "🇦🇺", code: "+61" },
      { name: "Austria", flag: "🇦🇹", code: "+43" },
      { name: "Azerbaijan", flag: "🇦🇿", code: "+994" },
      { name: "Bahamas", flag: "🇧🇸", code: "+1" },
      { name: "Bahrain", flag: "🇧🇭", code: "+973" },
      { name: "Bangladesh", flag: "🇧🇩", code: "+880" },
      { name: "Barbados", flag: "🇧🇧", code: "+1" },
      { name: "Belarus", flag: "🇧🇾", code: "+375" },
      { name: "Belgium", flag: "🇧🇪", code: "+32" },
      { name: "Belize", flag: "🇧🇿", code: "+501" },
      { name: "Benin", flag: "🇧🇯", code: "+229" },
      { name: "Bhutan", flag: "🇧🇹", code: "+975" },
      { name: "Bolivia", flag: "🇧🇴", code: "+591" },
      { name: "Bosnia and Herzegovina", flag: "🇧🇦", code: "+387" },
      { name: "Botswana", flag: "🇧🇼", code: "+267" },
      { name: "Brazil", flag: "🇧🇷", code: "+55" },
      { name: "Brunei", flag: "🇧🇳", code: "+673" },
      { name: "Bulgaria", flag: "🇧🇬", code: "+359" },
      { name: "Burkina Faso", flag: "🇧🇫", code: "+226" },
      { name: "Burundi", flag: "🇧🇮", code: "+257" },
      { name: "Cabo Verde", flag: "🇨🇻", code: "+238" },
      { name: "Cambodia", flag: "🇰🇭", code: "+855" },
      { name: "Cameroon", flag: "🇨🇲", code: "+237" },
      { name: "Canada", flag: "🇨🇦", code: "+1" },
      { name: "Central African Republic", flag: "🇨🇫", code: "+236" },
      { name: "Chad", flag: "🇹🇩", code: "+235" },
      { name: "Chile", flag: "🇨🇱", code: "+56" },
      { name: "China", flag: "🇨🇳", code: "+86" },
      { name: "Colombia", flag: "🇨🇴", code: "+57" },
      { name: "Comoros", flag: "🇰🇲", code: "+269" },
      { name: "Congo (Brazzaville)", flag: "🇨🇬", code: "+242" },
      { name: "Congo (Kinshasa)", flag: "🇨🇩", code: "+243" },
      { name: "Costa Rica", flag: "🇨🇷", code: "+506" },
      { name: "Croatia", flag: "🇭🇷", code: "+385" },
      { name: "Cuba", flag: "🇨🇺", code: "+53" },
      { name: "Cyprus", flag: "🇨🇾", code: "+357" },
      { name: "Czech Republic", flag: "🇨🇿", code: "+420" },
      { name: "Denmark", flag: "🇩🇰", code: "+45" },
      { name: "Djibouti", flag: "🇩🇯", code: "+253" },
      { name: "Dominica", flag: "🇩🇲", code: "+1" },
      { name: "Dominican Republic", flag: "🇩🇴", code: "+1" },
      { name: "Ecuador", flag: "🇪🇨", code: "+593" },
      { name: "Egypt", flag: "🇪🇬", code: "+20" },
      { name: "El Salvador", flag: "🇸🇻", code: "+503" },
      { name: "Equatorial Guinea", flag: "🇬🇶", code: "+240" },
      { name: "Eritrea", flag: "🇪🇷", code: "+291" },
      { name: "Estonia", flag: "🇪🇪", code: "+372" },
      { name: "Eswatini", flag: "🇸🇿", code: "+268" },
      { name: "Ethiopia", flag: "🇪🇹", code: "+251" },
      { name: "Fiji", flag: "🇫🇯", code: "+679" },
      { name: "Finland", flag: "🇫🇮", code: "+358" },
      { name: "France", flag: "🇫🇷", code: "+33" },
      { name: "Gabon", flag: "🇬🇦", code: "+241" },
      { name: "Gambia", flag: "🇬🇲", code: "+220" },
      { name: "Georgia", flag: "🇬🇪", code: "+995" },
      { name: "Germany", flag: "🇩🇪", code: "+49" },
      { name: "Ghana", flag: "🇬🇭", code: "+233" },
      { name: "Greece", flag: "🇬🇷", code: "+30" },
      { name: "Grenada", flag: "🇬🇩", code: "+1" },
      { name: "Guatemala", flag: "🇬🇹", code: "+502" },
      { name: "Guinea", flag: "🇬🇳", code: "+224" },
      { name: "Guinea-Bissau", flag: "🇬🇼", code: "+245" },
      { name: "Guyana", flag: "🇬🇾", code: "+592" },
      { name: "Haiti", flag: "🇭🇹", code: "+509" },
      { name: "Honduras", flag: "🇭🇳", code: "+504" },
      { name: "Hungary", flag: "🇭🇺", code: "+36" },
      { name: "Iceland", flag: "🇮🇸", code: "+354" },
      { name: "India", flag: "🇮🇳", code: "+91" },
      { name: "Indonesia", flag: "🇮🇩", code: "+62" },
      { name: "Iran", flag: "🇮🇷", code: "+98" },
      { name: "Iraq", flag: "🇮🇶", code: "+964" },
      { name: "Ireland", flag: "🇮🇪", code: "+353" },
      { name: "Israel", flag: "🇮🇱", code: "+972" },
      { name: "Italy", flag: "🇮🇹", code: "+39" },
      { name: "Jamaica", flag: "🇯🇲", code: "+1" },
      { name: "Japan", flag: "🇯🇵", code: "+81" },
      { name: "Jordan", flag: "🇯🇴", code: "+962" },
      { name: "Kazakhstan", flag: "🇰🇿", code: "+7" },
      { name: "Kenya", flag: "🇰🇪", code: "+254" },
      { name: "Kiribati", flag: "🇰🇮", code: "+686" },
      { name: "Kuwait", flag: "🇰🇼", code: "+965" },
      { name: "Kyrgyzstan", flag: "🇰🇬", code: "+996" },
      { name: "Laos", flag: "🇱🇦", code: "+856" },
      { name: "Latvia", flag: "🇱🇻", code: "+371" },
      { name: "Lebanon", flag: "🇱🇧", code: "+961" },
      { name: "Lesotho", flag: "🇱🇸", code: "+266" },
      { name: "Liberia", flag: "🇱🇷", code: "+231" },
      { name: "Libya", flag: "🇱🇾", code: "+218" },
      { name: "Liechtenstein", flag: "🇱🇮", code: "+423" },
      { name: "Lithuania", flag: "🇱🇹", code: "+370" },
      { name: "Luxembourg", flag: "🇱🇺", code: "+352" },
      { name: "Madagascar", flag: "🇲🇬", code: "+261" },
      { name: "Malawi", flag: "🇲🇼", code: "+265" },
      { name: "Malaysia", flag: "🇲🇾", code: "+60" },
      { name: "Maldives", flag: "🇲🇻", code: "+960" },
      { name: "Mali", flag: "🇲🇱", code: "+223" },
      { name: "Malta", flag: "🇲🇹", code: "+356" },
      { name: "Marshall Islands", flag: "🇲🇭", code: "+692" },
      { name: "Mauritania", flag: "🇲🇷", code: "+222" },
      { name: "Mauritius", flag: "🇲🇺", code: "+230" },
      { name: "Mexico", flag: "🇲🇽", code: "+52" },
      { name: "Micronesia", flag: "🇫🇲", code: "+691" },
      { name: "Moldova", flag: "🇲🇩", code: "+373" },
      { name: "Monaco", flag: "🇲🇨", code: "+377" },
      { name: "Mongolia", flag: "🇲🇳", code: "+976" },
      { name: "Montenegro", flag: "🇲🇪", code: "+382" },
      { name: "Morocco", flag: "🇲🇦", code: "+212" },
      { name: "Mozambique", flag: "🇲🇿", code: "+258" },
      { name: "Myanmar", flag: "🇲🇲", code: "+95" },
      { name: "Namibia", flag: "🇳🇦", code: "+264" },
      { name: "Nauru", flag: "🇳🇷", code: "+674" },
      { name: "Nepal", flag: "🇳🇵", code: "+977" },
      { name: "Netherlands", flag: "🇳🇱", code: "+31" },
      { name: "New Zealand", flag: "🇳🇿", code: "+64" },
      { name: "Nicaragua", flag: "🇳🇮", code: "+505" },
      { name: "Niger", flag: "🇳🇪", code: "+227" },
      { name: "Nigeria", flag: "🇳🇬", code: "+234" },
      { name: "North Korea", flag: "🇰🇵", code: "+850" },
      { name: "North Macedonia", flag: "🇲🇰", code: "+389" },
      { name: "Norway", flag: "🇳🇴", code: "+47" },
      { name: "Oman", flag: "🇴🇲", code: "+968" },
      { name: "Pakistan", flag: "🇵🇰", code: "+92" },
      { name: "Palau", flag: "🇵🇼", code: "+680" },
      { name: "Panama", flag: "🇵🇦", code: "+507" },
      { name: "Papua New Guinea", flag: "🇵🇬", code: "+675" },
      { name: "Paraguay", flag: "🇵🇾", code: "+595" },
      { name: "Peru", flag: "🇵🇪", code: "+51" },
      { name: "Philippines", flag: "🇵🇭", code: "+63" },
      { name: "Poland", flag: "🇵🇱", code: "+48" },
      { name: "Portugal", flag: "🇵🇹", code: "+351" },
      { name: "Qatar", flag: "🇶🇦", code: "+974" },
      { name: "Romania", flag: "🇷🇴", code: "+40" },
      { name: "Russia", flag: "🇷🇺", code: "+7" },
      { name: "Rwanda", flag: "🇷🇼", code: "+250" },
      { name: "Saint Kitts and Nevis", flag: "🇰🇳", code: "+1" },
      { name: "Saint Lucia", flag: "🇱🇨", code: "+1" },
      { name: "Saint Vincent and the Grenadines", flag: "🇻🇨", code: "+1" },
      { name: "Samoa", flag: "🇼🇸", code: "+685" },
      { name: "San Marino", flag: "🇸🇲", code: "+378" },
      { name: "Sao Tome and Principe", flag: "🇸🇹", code: "+239" },
      { name: "Saudi Arabia", flag: "🇸🇦", code: "+966" },
      { name: "Senegal", flag: "🇸🇳", code: "+221" },
      { name: "Serbia", flag: "🇷🇸", code: "+381" },
      { name: "Seychelles", flag: "🇸🇨", code: "+248" },
      { name: "Sierra Leone", flag: "🇸🇱", code: "+232" },
      { name: "Singapore", flag: "🇸🇬", code: "+65" },
      { name: "Slovakia", flag: "🇸🇰", code: "+421" },
      { name: "Slovenia", flag: "🇸🇮", code: "+386" },
      { name: "Solomon Islands", flag: "🇸🇧", code: "+677" },
      { name: "Somalia", flag: "🇸🇴", code: "+252" },
      { name: "South Africa", flag: "🇿🇦", code: "+27" },
      { name: "South Korea", flag: "🇰🇷", code: "+82" },
      { name: "South Sudan", flag: "🇸🇸", code: "+211" },
      { name: "Spain", flag: "🇪🇸", code: "+34" },
      { name: "Sri Lanka", flag: "🇱🇰", code: "+94" },
      { name: "Sudan", flag: "🇸🇩", code: "+249" },
      { name: "Suriname", flag: "🇸🇷", code: "+597" },
      { name: "Sweden", flag: "🇸🇪", code: "+46" },
      { name: "Switzerland", flag: "🇨🇭", code: "+41" },
      { name: "Syria", flag: "🇸🇾", code: "+963" },
      { name: "Taiwan", flag: "🇹🇼", code: "+886" },
      { name: "Tajikistan", flag: "🇹🇯", code: "+992" },
      { name: "Tanzania", flag: "🇹🇿", code: "+255" },
      { name: "Thailand", flag: "🇹🇭", code: "+66" },
      { name: "Timor-Leste", flag: "🇹🇱", code: "+670" },
      { name: "Togo", flag: "🇹🇬", code: "+228" },
      { name: "Tonga", flag: "🇹🇴", code: "+676" },
      { name: "Trinidad and Tobago", flag: "🇹🇹", code: "+1" },
      { name: "Tunisia", flag: "🇹🇳", code: "+216" },
      { name: "Turkey", flag: "🇹🇷", code: "+90" },
      { name: "Turkmenistan", flag: "🇹🇲", code: "+993" },
      { name: "Tuvalu", flag: "🇹🇻", code: "+688" },
      { name: "Uganda", flag: "🇺🇬", code: "+256" },
      { name: "Ukraine", flag: "🇺🇦", code: "+380" },
      { name: "United Arab Emirates", flag: "🇦🇪", code: "+971" },
      { name: "United Kingdom", flag: "🇬🇧", code: "+44" },
      { name: "United States", flag: "🇺🇸", code: "+1" },
      { name: "Uruguay", flag: "🇺🇾", code: "+598" },
      { name: "Uzbekistan", flag: "🇺🇿", code: "+998" },
      { name: "Vanuatu", flag: "🇻🇺", code: "+678" },
      { name: "Vatican City", flag: "🇻🇦", code: "+39" },
      { name: "Venezuela", flag: "🇻🇪", code: "+58" },
      { name: "Vietnam", flag: "🇻🇳", code: "+84" },
      { name: "Yemen", flag: "🇾🇪", code: "+967" },
      { name: "Zambia", flag: "🇿🇲", code: "+260" },
      { name: "Zimbabwe", flag: "🇿🇼", code: "+263" }
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
          
          // Auto-populate phone number field with country code
          setTimeout(() => {
            const contactInput = document.getElementById("contactInput");
            
            if (contactInput) {
              const currentValue = contactInput.value.trim();
              
              // Only add country code if field is empty or doesn't already start with a +
              if (!currentValue || !currentValue.startsWith('+')) {
                contactInput.value = c.code + ' ';
                contactInput.focus();
              }
            }
          }, 10);
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