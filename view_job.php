<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: employee_login.php");
    exit();
}

require_once('db_connect.php');

if (!isset($_GET['id'])) {
    header("Location: browse_jobs.php");
    exit();
}

$job_id = $_GET['id'];

// Fetch job details
$stmt = $conn->prepare("
    SELECT j.*, e.Name as employer_name, e.Location as employer_location,
           CASE WHEN ja.ID IS NOT NULL THEN 1 ELSE 0 END as has_applied
    FROM jobs j 
    JOIN employer e ON j.Employer_ID = e.ID
    LEFT JOIN job_applications ja ON j.ID = ja.Job_ID AND ja.Employee_ID = ?
    WHERE j.ID = ? AND j.Status = 'active' AND j.Expiry_date >= CURDATE()
");
$stmt->execute([$_SESSION['employee_id'], $job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header("Location: browse_jobs.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($job['Title']) ?> - Houselp Connect</title>
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
        
        .job-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .job-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin: 0 0 10px 0;
        }
        
        .employer-name {
            color: #666;
            font-size: 18px;
            margin-bottom: 20px;
        }
        
        .job-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .meta-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        
        .meta-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .meta-value {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }
        
        .salary {
            color: #28a745;
            font-weight: bold;
        }
        
        .job-description {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid rgb(24, 123, 136);
            padding-bottom: 10px;
        }
        
        .description-text {
            line-height: 1.6;
            color: #444;
            font-size: 16px;
        }
        
        .job-actions {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .btn {
            background: rgb(24, 123, 136);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            font-weight: bold;
            margin: 0 10px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background: rgb(20, 100, 110);
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        .applied-badge {
            background: #28a745;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 20px;
            display: inline-block;
        }
        
        .expiry-warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
    </style>
    <link rel="stylesheet" href="responsive.css?v=2">
    <script src="hamburger.js" defer></script>
</head>
<body>

<header>
    <div class="logo">Houselp Connect</div>
    <nav class="main-nav">
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="faq.php">FAQ</a></li>
        </ul>
    </nav>
</header>

<div class="container">
    <div class="job-header">
        <h1 class="job-title"><?= htmlspecialchars($job['Title']) ?></h1>
        <p class="employer-name">Posted by <?= htmlspecialchars($job['employer_name']) ?></p>
        
        <?php if ($job['has_applied']): ?>
            <div class="applied-badge">✓ You have already applied for this job</div>
        <?php endif; ?>
        
        <?php 
        $days_until_expiry = (strtotime($job['Expiry_date']) - time()) / (60 * 60 * 24);
        if ($days_until_expiry <= 3 && $days_until_expiry > 0): 
        ?>
            <div class="expiry-warning">
                ⚠️ This job expires in <?= round($days_until_expiry) ?> day<?= round($days_until_expiry) != 1 ? 's' : '' ?>
            </div>
        <?php endif; ?>
        
        <div class="job-meta">
            <div class="meta-item">
                <div class="meta-label">Location</div>
                <div class="meta-value"><?= htmlspecialchars($job['Location']) ?></div>
            </div>
            
            <div class="meta-item">
                <div class="meta-label">Job Type</div>
                <div class="meta-value"><?= ucfirst(str_replace('-', ' ', $job['Job_type'])) ?></div>
            </div>
            
            <div class="meta-item">
                <div class="meta-label">Start Date</div>
                <div class="meta-value"><?= date('M j, Y', strtotime($job['Start_date'])) ?></div>
            </div>
            
            <div class="meta-item">
                <div class="meta-label">Required Skills</div>
                <div class="meta-value"><?= htmlspecialchars($job['Required_skills']) ?></div>
            </div>
            
            <?php if ($job['Duration_hours']): ?>
            <div class="meta-item">
                <div class="meta-label">Duration</div>
                <div class="meta-value"><?= $job['Duration_hours'] ?> hours</div>
            </div>
            <?php endif; ?>
            
            <?php if ($job['Salary_min'] || $job['Salary_max']): ?>
            <div class="meta-item">
                <div class="meta-label">Salary Range</div>
                <div class="meta-value salary">
                    KSH <?= $job['Salary_min'] ? number_format($job['Salary_min']) : '0' ?> - 
                    <?= $job['Salary_max'] ? number_format($job['Salary_max']) : 'Negotiable' ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="meta-item">
                <div class="meta-label">Expires</div>
                <div class="meta-value"><?= date('M j, Y', strtotime($job['Expiry_date'])) ?></div>
            </div>
        </div>
    </div>
    
    <div class="job-description">
        <h2 class="section-title">Job Description</h2>
        <div class="description-text">
            <?= nl2br(htmlspecialchars($job['Description'])) ?>
        </div>
        
        <?php if ($job['Special_requirements']): ?>
            <h3 style="margin-top: 30px; color: #333;">Special Requirements</h3>
            <div class="description-text">
                <?= nl2br(htmlspecialchars($job['Special_requirements'])) ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="job-actions">
        <a href="browse_jobs.php" class="btn">Back to Jobs</a>
        
        <?php if (!$job['has_applied']): ?>
            <a href="apply_job.php?job_id=<?= $job['ID'] ?>" class="btn btn-success">Apply Now</a>
        <?php else: ?>
            <button class="btn btn-disabled" disabled>Already Applied</button>
        <?php endif; ?>
    </div>
</div>

</body>
</html> 