<?php
session_start();
require_once 'db_connect.php';

// Only allow logged-in agents
if (!isset($_SESSION['agent_id'])) {
    header('Location: agent_login.php');
    exit();
}

$agent_id = $_SESSION['agent_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form data
    $name = trim($_POST['name']);
    $gender = $_POST['gender'];
    $age = intval($_POST['age']);
    $phone = trim($_POST['phone']);
    $national_id = trim($_POST['national_id']);
    $country = trim($_POST['country']);
    $county_province = trim($_POST['county_province']);
    $skills = trim($_POST['skills']);
    $experience = trim($_POST['experience']);
    $education_level = trim($_POST['education_level']);
    $social_referee = trim($_POST['social_referee']);
    $language = trim($_POST['language']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $residence_type = $_POST['residence_type'];
    $status = 'active';
    $verification_status = 'pending';
    $created_at = date('Y-m-d H:i:s');

    // Validate required fields
    if (!$name || !$gender || $age < 18 || !$phone || !$country || !$county_province || !$skills || !$education_level || !$email || !$password || !$residence_type) {
        $error = 'Please fill in all required fields and ensure age is 18 or above.';
    } elseif (empty($national_id)) {
        $error = 'National ID/Passport Number is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!isset($_FILES['id_passport']) || $_FILES['id_passport']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please upload an ID/Passport document or image.';
    } else {
        // Handle file upload
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileTmp = $_FILES['id_passport']['tmp_name'];
        $fileName = uniqid('id_') . '_' . basename($_FILES['id_passport']['name']);
        $uploadFile = $uploadDir . $fileName;
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'image/jpg'];
        $fileType = mime_content_type($fileTmp);
        if (!in_array($fileType, $allowedTypes)) {
            $error = 'Only JPG, PNG, or PDF files are allowed.';
        } elseif (!move_uploaded_file($fileTmp, $uploadFile)) {
            $error = 'Failed to upload ID/Passport document.';
        } else {
            $profile_pic_path = null;
            // Handle profile picture upload
            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                $profilePicDir = 'uploads/';
                if (!is_dir($profilePicDir)) {
                    mkdir($profilePicDir, 0777, true);
                }
                $profilePicTmp = $_FILES['profile_pic']['tmp_name'];
                $profilePicName = uniqid('profile_') . '_' . basename($_FILES['profile_pic']['name']);
                $profilePicFile = $profilePicDir . $profilePicName;
                $allowedPicTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                $profilePicType = mime_content_type($profilePicTmp);
                if (in_array($profilePicType, $allowedPicTypes) && move_uploaded_file($profilePicTmp, $profilePicFile)) {
                    $profile_pic_path = $profilePicFile;
                }
            }
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO employees (name, gender, age, phone, country, county_province, skills, experience, education_level, social_referee, language, email, password_hash, residence_type, verification_status, created_at, agent_id, id_passport, Profile_pic, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt = $conn->prepare("INSERT INTO employees (name, gender, age, phone, National_id, country, county_province, skills, experience, education_level, social_referee, language, email, password_hash, residence_type, verification_status, created_at, agent_id, id_passport, Profile_pic, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                $name, $gender, $age, $phone, $national_id, $country, $county_province, $skills, $experience, $education_level, $social_referee, $language, $email, $password_hash, $residence_type, $verification_status, $created_at, $agent_id, $fileName, $profile_pic_path, $status
            ]);
            if ($result) {
                $success = 'Employee registered successfully!';
            } else {
                $error = 'Failed to register employee. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register New Employee - Homeworker Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background: #f4f8fb;
            font-family: 'Segoe UI', Arial, sans-serif;
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
        .form-container h2 {
            text-align: center;
            color: #197b88;
            margin-bottom: 16px;
            border: none !important;
            border-bottom: none !important;
            box-shadow: none !important;
            outline: none !important;
            background: none !important;
        }
        form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 18px;
        }
        label {
            font-weight: 500;
            color: #333;
            margin-bottom: 4px;
        }
        input[type="text"],
        input[type="number"],
        input[type="email"],
        input[type="password"],
        input[type="file"],
        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cfd8dc;
            border-radius: 6px;
            font-size: 1rem;
            background: #f9fbfc;
            transition: border 0.2s;
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        select:focus {
            border: 1.5px solid #197b88;
            outline: none;
            background: #fff;
        }
        .btn {
            background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 12px 0;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s;
        }
        .btn:hover {
            background: linear-gradient(90deg, #1ec8c8 0%, #197b88 100%);
        }
        .error {
            background: #ffeaea;
            color: #c0392b;
            border-left: 4px solid #c0392b;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .success {
            background: #eafaf1;
            color: #218c5b;
            border-left: 4px solid #218c5b;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        @media (max-width: 600px) {
            .form-container {
                padding: 16px 6vw 18px 6vw;
            }
            form {
                gap: 12px;
            }
        }
        #countryList.country-dropdown {
            position: absolute;
            left: 0;
            right: 0;
            top: 100%;
            z-index: 10;
            background: #fff;
            border: 1px solid #cfd8dc;
            border-radius: 0 0 6px 6px;
            width: 100%;
            max-height: 180px;
            overflow-y: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            margin-top: 2px;
        }
        #countryList.country-dropdown li {
            padding: 8px 14px;
            cursor: pointer;
            font-size: 1rem;
        }
        #countryList.country-dropdown li:hover {
            background: #f1f7fa;
        }
        .country-dropdown {
            border: none !important;
            border-top: none !important;
            box-shadow: none !important;
            background: #fff !important;
            margin-top: 0 !important;
        }
        .country-dropdown li {
            border: none !important;
            box-shadow: none !important;
        }
    </style>
</head>
<body style="background: #f4f8fb; font-family: 'Segoe UI', Arial, sans-serif;">
    <header>
        <div class="logo">
            <img src="bghse.png" alt="Logo" style="height: 40px; margin-right: 10px;">
            
        </div>
        <nav class="main-nav">
            <ul class="nav-links">
                <li><a href="agent_dashboard.php">Dashboard</a></li>
                <li><a href="manage_employees.php">Manage Employees</a></li>
                <li><a href="agent_logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
    <div class="form-card">
        <h2>Register New Employee</h2>
        <?php if ($error): ?>
            <p class="error-message"><?= htmlspecialchars($error) ?></p>
        <?php elseif ($success): ?>
            <p class="success-message"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>
        <form method="POST" action="" enctype="multipart/form-data" autocomplete="off">
            <div class="form-group">
                <label for="national_id">National ID/Passport Number</label>
                <input type="text" name="national_id" id="national_id" required value="<?= isset($_POST['national_id']) ? htmlspecialchars($_POST['national_id']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" required value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select name="gender" id="gender" required>
                    <option value="">Select Gender</option>
                    <option value="male" <?= (isset($_POST['gender']) && $_POST['gender'] == 'male') ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= (isset($_POST['gender']) && $_POST['gender'] == 'female') ? 'selected' : '' ?>>Female</option>
                </select>
            </div>
            <div class="form-group">
                <label for="age">Age (18+)</label>
                <input type="number" name="age" id="age" min="18" max="99" required value="<?= isset($_POST['age']) ? htmlspecialchars($_POST['age']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" name="phone" id="phone" required value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="countryInput">Country</label>
                <div style="position:relative;">
                  <input type="text" id="countryInput" name="country" placeholder="Country" autocomplete="off" required value="<?= isset($_POST['country']) ? htmlspecialchars($_POST['country']) : '' ?>">
                  <ul id="countryList" class="country-dropdown"></ul>
                </div>
            </div>
            <div class="form-group">
                <label for="county_province">County/Province</label>
                <input type="text" name="county_province" id="county_province" required value="<?= isset($_POST['county_province']) ? htmlspecialchars($_POST['county_province']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="skills">Job Title</label>
                <select name="skills" id="skills" required onchange="toggleSpecifyJobTitle(this.value)">
                    <option value="">Select Job Title</option>
                    <option value="Housegirl" <?= (isset($_POST['skills']) && $_POST['skills'] == 'Housegirl') ? 'selected' : '' ?>>Housegirl</option>
                    <option value="Houseboy" <?= (isset($_POST['skills']) && $_POST['skills'] == 'Houseboy') ? 'selected' : '' ?>>Houseboy</option>
                    <option value="Shambaboy" <?= (isset($_POST['skills']) && $_POST['skills'] == 'Shambaboy') ? 'selected' : '' ?>>Shambaboy</option>
                    <option value="Gatekeeper" <?= (isset($_POST['skills']) && $_POST['skills'] == 'Gatekeeper') ? 'selected' : '' ?>>Gatekeeper</option>
                    <option value="Cook" <?= (isset($_POST['skills']) && $_POST['skills'] == 'Cook') ? 'selected' : '' ?>>Cook</option>
                    <option value="Gardener" <?= (isset($_POST['skills']) && $_POST['skills'] == 'Gardener') ? 'selected' : '' ?>>Gardener</option>
                    <option value="Driver" <?= (isset($_POST['skills']) && $_POST['skills'] == 'Driver') ? 'selected' : '' ?>>Driver</option>
                    <option value="Nanny" <?= (isset($_POST['skills']) && $_POST['skills'] == 'Nanny') ? 'selected' : '' ?>>Nanny</option>
                    <option value="Cleaner" <?= (isset($_POST['skills']) && $_POST['skills'] == 'Cleaner') ? 'selected' : '' ?>>Cleaner</option>
                    <option value="Other" <?= (isset($_POST['skills']) && $_POST['skills'] == 'Other' || (isset($_POST['skills_specify']) && $_POST['skills_specify'] != '')) ? 'selected' : '' ?>>Other (Specify)</option>
                </select>
                <input type="text" name="skills_specify" id="skills_specify" placeholder="Please specify job title" style="display:none;margin-top:8px;" value="<?= isset($_POST['skills_specify']) ? htmlspecialchars($_POST['skills_specify']) : '' ?>" oninput="if(this.value){document.getElementById('skills').value='Other';toggleSpecifyJobTitle('Other');}">
            </div>
<!-- Job Title Specify Script moved below -->
<script>
function toggleSpecifyJobTitle(val) {
  var specify = document.getElementById('skills_specify');
  if (val === 'Other') {
    specify.style.display = 'block';
    specify.disabled = false;
    specify.required = true;
    specify.focus();
  } else {
    specify.style.display = 'none';
    specify.disabled = true;
    specify.required = false;
    specify.value = '';
  }
}
// On page load, show specify if Other was selected or if skills_specify has a value
window.addEventListener('DOMContentLoaded', function() {
  var sel = document.getElementById('skills');
  var specify = document.getElementById('skills_specify');
  if ((sel && sel.value === 'Other') || (specify && specify.value)) {
    toggleSpecifyJobTitle('Other');
  }
});
</script>
            <div class="form-group">
                <label for="experience">Experience</label>
                <input type="text" name="experience" id="experience" placeholder="e.g. 5 years" value="<?= isset($_POST['experience']) ? htmlspecialchars($_POST['experience']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="education_level">Education Level</label>
                <select name="education_level" id="education_level" required>
                    <option value="">Select Education Level</option>
                    <option value="Primary" <?= (isset($_POST['education_level']) && $_POST['education_level'] == 'Primary') ? 'selected' : '' ?>>Primary</option>
                    <option value="Secondary" <?= (isset($_POST['education_level']) && $_POST['education_level'] == 'Secondary') ? 'selected' : '' ?>>Secondary</option>
                    <option value="Tertiary" <?= (isset($_POST['education_level']) && $_POST['education_level'] == 'Tertiary') ? 'selected' : '' ?>>Tertiary</option>
                </select>
            </div>
            <div class="form-group">
                <label for="social_referee">Social Referee Contact</label>
                <input type="text" name="social_referee" id="social_referee" placeholder="Social Referee Contact" value="<?= isset($_POST['social_referee']) ? htmlspecialchars($_POST['social_referee']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="language-input">Languages</label>
                <input type="text" id="language-input" placeholder="Type and select languages">
                <input type="hidden" id="language" name="language" value="<?= isset($_POST['language']) ? htmlspecialchars($_POST['language']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" required placeholder="e.g. janedoe@email.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
        <div style="position:relative;">
          <input type="password" name="password" id="password" placeholder="Password" required style="padding-right:36px;">
          <span onclick="togglePassword('password', this)" style="position:absolute;top:50%;right:10px;transform:translateY(-50%);cursor:pointer;font-size:1.2em;">&#128065;</span>
        </div>
            </div>
            <div class="form-group">
                <label for="residence_type">Residence Type</label>
                <select name="residence_type" id="residence_type" required>
                    <option value="">Residence Type</option>
                    <option value="urban" <?= (isset($_POST['residence_type']) && $_POST['residence_type'] == 'urban') ? 'selected' : '' ?>>Urban</option>
                    <option value="rural" <?= (isset($_POST['residence_type']) && $_POST['residence_type'] == 'rural') ? 'selected' : '' ?>>Rural</option>
                </select>
            </div>
            <div class="form-group">
                <label for="id_passport">ID/Passport (Upload or Take Photo)</label>
                <input type="file" name="id_passport" id="id_passport" accept="image/*,.pdf" capture="environment" required>
            </div>
            <div class="form-group">
                <label for="profile_pic">Profile Picture (optional)</label>
                <input type="file" name="profile_pic" id="profile_pic" accept="image/*">
            </div>
            <input type="hidden" name="status" value="active">
            <input type="hidden" name="verification_status" value="pending">
            <button type="submit" class="btn-pro">Register Employee</button>
        </form>
    </div>
    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> Homeworker Connect. All rights reserved.</p>
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
    <style>
        .form-card {
            max-width: 500px;
            margin: 40px auto 30px auto;
            background: #fff;
            border-radius: 12px;
            padding: 32px 28px 24px 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .form-card h2 {
            text-align: center;
            color: #197b88;
            margin-bottom: 18px;
            border: none;
            border-bottom: none;
            box-shadow: none;
            outline: none;
            background: none;
        }
        .form-group {
            width: 100%;
            display: flex;
            flex-direction: column;
            margin-bottom: 12px;
        }
        .form-group label {
            font-weight: 500;
            color: #333;
            margin-bottom: 4px;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="file"],
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cfd8dc;
            border-radius: 6px;
            font-size: 1rem;
            background: #f9fbfc;
            transition: border 0.2s;
        }
        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group input[type="email"]:focus,
        .form-group input[type="password"]:focus,
        .form-group select:focus {
            border: 1.5px solid #197b88;
            outline: none;
            background: #fff;
        }
        .btn-pro {
            background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 12px 0;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            width: 100%;
            transition: background 0.2s;
        }
        .btn-pro:hover {
            background: linear-gradient(90deg, #1ec8c8 0%, #197b88 100%);
        }
        .error-message {
            background: #ffeaea;
            color: #c0392b;
            border-left: 4px solid #c0392b;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 10px;
            width: 100%;
        }
        .success-message {
            background: #eafaf1;
            color: #218c5b;
            border-left: 4px solid #218c5b;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 10px;
            width: 100%;
        }
        @media (max-width: 600px) {
            .form-card {
                padding: 16px 6vw 18px 6vw;
            }
        }
    </style>
    <!-- Country dropdown and language tag input scripts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons/css/flag-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
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
</body>
</html>