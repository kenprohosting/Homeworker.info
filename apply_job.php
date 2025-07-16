<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: employee_access.php");
    exit();
}

require_once('db_connect.php');

$message = '';
$job = null;

if (!isset($_GET['job_id'])) {
    header("Location: browse_jobs.php");
    exit();
}

$job_id = $_GET['job_id'];

// Fetch job details
$stmt = $conn->prepare("
    SELECT j.*, e.Name as employer_name, e.Location as employer_location
    FROM jobs j 
    JOIN employer e ON j.Employer_ID = e.ID
    WHERE j.ID = ? AND j.Status = 'active' AND j.Expiry_date >= CURDATE()
");
$stmt->execute([$job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header("Location: browse_jobs.php");
    exit();
}

// Check if already applied
$check_stmt = $conn->prepare("SELECT ID FROM job_applications WHERE Job_ID = ? AND Employee_ID = ?");
$check_stmt->execute([$job_id, $_SESSION['employee_id']]);
if ($check_stmt->fetch()) {
    header("Location: browse_jobs.php");
    exit();
}

// Handle application submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cover_letter = trim($_POST['cover_letter']);
    
    if (empty($cover_letter)) {
        $message = '<div style="color: red; margin: 10px 0;">Please provide a cover letter.</div>';
    } else {
        $stmt = $conn->prepare("
            INSERT INTO job_applications (Job_ID, Employee_ID, Cover_letter) 
            VALUES (?, ?, ?)
        ");
        
        if ($stmt->execute([$job_id, $_SESSION['employee_id'], $cover_letter])) {
            $message = '<div style="color: green; margin: 10px 0;">Application submitted successfully!</div>';
        } else {
            $message = '<div style="color: red; margin: 10px 0;">Error submitting application. Please try again.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Apply for Job - Houselp Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        header {
            background: rgb(24, 123, 136);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5em;
            font-weight: bold;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .job-summary {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .job-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 0 0 10px 0;
        }
        
        .employer-name {
            color: #666;
            font-size: 16px;
            margin-bottom: 15px;
        }
        
        .job-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .job-details p {
            margin: 5px 0;
            color: #666;
        }
        
        .job-details strong {
            color: #333;
        }
        
        .salary {
            color: #28a745;
            font-weight: bold;
        }
        
        .job-description {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            line-height: 1.6;
        }
        
        .application-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: rgb(24, 123, 136);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        
        textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            font-family: inherit;
            resize: vertical;
            min-height: 200px;
            box-sizing: border-box;
        }
        
        .btn {
            background: rgb(24, 123, 136);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .btn:hover {
            background: rgb(20, 100, 110);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .help-text {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .required {
            color: red;
        }
    </style>
</head>
<body>
    <button class="nav-btn" onclick="window.history.back()">← Back</button>

<header>
    <div class="logo">Houselp Connect</div>
    <nav>
        <a href="employee_dashboard.php">Dashboard</a>
        <a href="browse_jobs.php">Browse Jobs</a>
        <a href="employee_logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h1>Apply for Job</h1>
    
    <div class="job-summary">
        <h2 class="job-title"><?= htmlspecialchars($job['Title']) ?></h2>
        <p class="employer-name">Posted by <?= htmlspecialchars($job['employer_name']) ?></p>
        
        <div class="job-details">
            <p><strong>Location:</strong> <?= htmlspecialchars($job['Location']) ?></p>
            <p><strong>Type:</strong> <?= ucfirst(str_replace('-', ' ', $job['Job_type'])) ?></p>
            <p><strong>Start Date:</strong> <?= date('M j, Y', strtotime($job['Start_date'])) ?></p>
            <p><strong>Required Skills:</strong> <?= htmlspecialchars($job['Required_skills']) ?></p>
            <?php if ($job['Duration_hours']): ?>
                <p><strong>Duration:</strong> <?= $job['Duration_hours'] ?> hours</p>
            <?php endif; ?>
            <?php if ($job['Salary_min'] || $job['Salary_max']): ?>
                <p class="salary">
                    <strong>Salary:</strong> 
                    KSH <?= $job['Salary_min'] ? number_format($job['Salary_min']) : '0' ?> - 
                    <?= $job['Salary_max'] ? number_format($job['Salary_max']) : 'Negotiable' ?>
                </p>
            <?php endif; ?>
            <p><strong>Expires:</strong> <?= date('M j, Y', strtotime($job['Expiry_date'])) ?></p>
        </div>
        
        <div class="job-description">
            <strong>Job Description:</strong><br>
            <?= nl2br(htmlspecialchars($job['Description'])) ?>
        </div>
        
        <?php if ($job['Special_requirements']): ?>
            <p><strong>Special Requirements:</strong> <?= htmlspecialchars($job['Special_requirements']) ?></p>
        <?php endif; ?>
    </div>
    
    <div class="application-form">
        <h3>Submit Your Application</h3>
        
        <?php echo $message; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="cover_letter">Cover Letter <span class="required">*</span></label>
                <textarea id="cover_letter" name="cover_letter" required 
                          placeholder="Introduce yourself, explain why you're interested in this job, and highlight your relevant skills and experience..."></textarea>
                <div class="help-text">
                    Tell the employer why you're the best candidate for this position. 
                    Include your relevant experience, skills, and why you're interested in this job.
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Submit Application</button>
                <a href="browse_jobs.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

</body>
</html> 