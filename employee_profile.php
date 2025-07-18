<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: employee_login.php");
    exit();
}
require_once("db_connect.php");

$employee_id = $_SESSION['employee_id'];
$success = '';
$errors = [];

// Get employee data
$stmt = $conn->prepare("SELECT * FROM employees WHERE ID = ?");
$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $skills = $_POST['skills'];
    $country = $_POST['country'];
    $county_province = $_POST['county_province'];
    $language = $_POST['language'];
    $education = $_POST['education'];
    $residence = $_POST['residence_type'];

    $profile_pic = $employee['Profile_pic'] ?? null;

    // Upload new picture
    if (!empty($_FILES['profile_pic']['name'])) {
        $targetDir = "uploads/";
        $filename = basename($_FILES["profile_pic"]["name"]);
        $uniqueName = time() . "_" . $filename;
        $targetFile = $targetDir . $uniqueName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
            $errors[] = "Only JPG, JPEG, and PNG files are allowed.";
        } elseif (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile)) {
            $profile_pic = $targetFile;
        } else {
            $errors[] = "Failed to upload profile picture.";
        }
    }

    if (empty($errors)) {
        $update = $conn->prepare("UPDATE employees SET Name = ?, Age = ?, Skills = ?, country = ?, county_province = ?, Language = ?, Education_level = ?, residence_type = ?, Profile_pic = ? WHERE ID = ?");
        $result = $update->execute([$name, $age, $skills, $country, $county_province, $language, $education, $residence, $profile_pic, $employee_id]);

        if ($result) {
            $success = "Profile updated successfully!";
            $stmt->execute([$employee_id]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC); // Refresh
        } else {
            $errors[] = "Update failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="favicon.png">
    <title>Employee Profile - Homeworker Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Completely override body styling */
        body {
            border: none !important;
            outline: none !important;
            margin: 0 !important;
            padding: 0 !important;
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
            background-color: #f8f9fa !important;
            font-family: 'Segoe UI', sans-serif !important;
        }
        
        html {
            border: none !important;
            outline: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Remove any viewport or container borders */
        * {
            box-sizing: border-box;
        }
        
        /* Override main CSS borders */
        .container, main, section, div {
            border: none !important;
        }
        
        /* Remove header borders */
        header {
            border: none !important;
        }
        
        /* Remove footer borders */
        footer {
            border: none !important;
        }
        
        /* Add essential header and navigation styling */
        header {
            background-color: #0A4A70 !important;
            color: white !important;
            padding: 15px 20px !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            position: relative !important;
        }
        
        .logo {
            font-size: 1.5rem !important;
            font-weight: bold !important;
            display: flex !important;
            align-items: center !important;
        }
        
        .main-nav {
            margin-left: auto !important;
            display: flex !important;
            align-items: center !important;
        }
        
        .nav-btn {
            background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%) !important;
            color: #fff !important;
            padding: 10px 22px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            text-decoration: none !important;
            margin: 0 6px !important;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s, border 0.2s !important;
            box-shadow: 0 2px 8px rgba(24,123,136,0.10) !important;
            border: 2px solid #197b88 !important;
            display: inline-block !important;
            cursor: pointer !important;
        }
        
        .nav-btn:hover, .nav-btn:focus {
            background: linear-gradient(90deg, #17606e 0%, #1ec8c8 100%) !important;
            color: #ffd700 !important;
            box-shadow: 0 4px 16px rgba(24,123,136,0.16) !important;
            outline: none !important;
            border-color: #125a66 !important;
        }
        
        /* Footer styling */
        footer {
            background-color: #0A4A70 !important;
            color: white !important;
            text-align: center !important;
            padding: 15px 0 !important;
            margin-top: auto !important;
        }
        
        /* User greeting */
        .user-greeting {
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            font-family: 'Segoe UI', sans-serif;
        }
        
        /* Ensure navigation buttons remain visible and functional */
        .nav-links {
            display: flex !important;
            list-style: none !important;
            gap: 20px;
            align-items: center;
            margin: 0;
            padding: 0;
        }
        
        .nav-btn {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        /* Main content styling */
        .form-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
            flex: 1;
        }
        
        h2 {
            color: #197b88;
            font-size: 2rem;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }
        
        .profile-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto 20px auto;
            border: 4px solid #197b88;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: 'Segoe UI', sans-serif;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: #197b88;
            outline: none;
            box-shadow: 0 0 0 2px rgba(25, 123, 136, 0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .btn {
            background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            display: block;
            margin: 20px auto 0 auto;
        }
        
        .btn:hover {
            background: linear-gradient(90deg, #17606e 0%, #1ec8c8 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(24,123,136,0.3);
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #f5c6cb;
        }
        
        /* Fix for mobile responsiveness */
        @media (max-width: 768px) {
            .nav-links {
                flex-direction: column;
                width: 100%;
                gap: 10px;
            }
            
            .nav-btn {
                width: 100%;
                text-align: center;
                margin: 0;
                padding: 12px 20px;
            }
            
            .form-container {
                padding: 0 15px;
            }
            
            .profile-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <img src="bghse.png" alt="Logo" style="height: 40px;">
    </div>
    <nav class="main-nav">
        <ul class="nav-links">
            <li><span class="user-greeting">Hello, <?= htmlspecialchars($_SESSION['employee_name']) ?></span></li>
            <li><a class="nav-btn" href="employee_dashboard.php">Dashboard</a></li>
            <li><a class="nav-btn" href="browse_jobs.php">Browse Jobs</a></li>
            <li><a class="nav-btn" href="my_applications.php">My Applications</a></li>
            <li><a class="nav-btn" href="employee_logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<div class="form-container">
    <h2>Update Your Profile</h2>

    <div class="profile-form">
        <?php if ($success) echo "<div class='success'>$success</div>"; ?>
        <?php foreach ($errors as $e) echo "<div class='error'>$e</div>"; ?>

        <?php
        $profilePath = $employee['Profile_pic'] ?? '';
        if (!empty($profilePath) && file_exists($profilePath)):
        ?>
            <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile Picture" class="profile-pic">
        <?php else: ?>
            <img src="uploads/default.jpg" class="profile-pic" alt="Default Picture">
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Name:</label>
                <input name="name" value="<?= htmlspecialchars($employee['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Age:</label>
                <input type="number" name="age" value="<?= htmlspecialchars($employee['age'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Skills:</label>
                <textarea name="skills" required><?= htmlspecialchars($employee['skills'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Country:</label>
                <input name="country" value="<?= htmlspecialchars($employee['country'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>County/Province:</label>
                <input name="county_province" value="<?= htmlspecialchars($employee['county_province'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Languages:</label>
                <select name="language" required>
                    <option <?= (isset($employee['language']) && $employee['language'] == 'English') ? 'selected' : '' ?>>English</option>
                    <option <?= (isset($employee['language']) && $employee['language'] == 'Kiswahili') ? 'selected' : '' ?>>Kiswahili</option>
                    <option <?= (isset($employee['language']) && $employee['language'] == 'Both') ? 'selected' : '' ?>>Both</option>
                </select>
            </div>

            <div class="form-group">
                <label>Education Level:</label>
                <input name="education" value="<?= htmlspecialchars($employee['education_level'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Residence Type:</label>
                <select name="residence_type" required>
                    <option <?= (isset($employee['residence_type']) && $employee['residence_type'] == 'urban') ? 'selected' : '' ?>>urban</option>
                    <option <?= (isset($employee['residence_type']) && $employee['residence_type'] == 'rural') ? 'selected' : '' ?>>rural</option>
                </select>
            </div>

            <div class="form-group">
                <label>Profile Picture:</label>
                <input type="file" name="profile_pic" accept="image/*">
            </div>

            <button type="submit" class="btn">Update Profile</button>
        </form>
    </div>
</div>

<footer>
    <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
</footer>

</body>
</html>
