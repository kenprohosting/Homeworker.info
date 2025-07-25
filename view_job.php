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
    <title><?= htmlspecialchars($job['Title']) ?> - Homeworker Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
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
        * { box-sizing: border-box; }
        .container, main, section, div { border: none !important; }
        header { border: none !important; }
        footer { border: none !important; }
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
        .user-greeting {
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            font-family: 'Segoe UI', sans-serif;
        }
        .profile-img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            margin-left: 15px;
        }
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
        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .job-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(24,123,136,0.10);
            padding: 32px 28px 28px 28px;
            margin-bottom: 30px;
        }
        .job-title {
            color: #197b88;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .employer-name {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 18px;
        }
        .job-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }
        .meta-item {
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 8px;
        }
        .meta-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .meta-value {
            font-size: 15px;
            color: #333;
            font-weight: 500;
        }
        .salary {
            color: #28a745;
            font-weight: bold;
        }
        .section-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #197b88;
            margin-bottom: 12px;
            border-bottom: 2px solid #197b88;
            padding-bottom: 6px;
        }
        .description-text {
            line-height: 1.6;
            color: #444;
            font-size: 1rem;
        }
        .job-actions {
            background: #f8f9fa;
            padding: 24px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            margin-top: 18px;
        }
        .btn {
            background: rgb(24, 123, 136);
            color: white;
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            font-size: 1rem;
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
            .container {
                padding: 0 6px;
            }
            .job-card {
                padding: 16px 4px 18px 4px;
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
            <li><a class="nav-btn" href="browse_jobs.php">Browse Jobs</a></li>
            <li><a class="nav-btn" href="my_applications.php">My Applications</a></li>
            <li><a class="nav-btn" href="employee_profile.php">Update Profile</a></li>
            <li><a class="nav-btn" href="employee_logout.php">Logout</a></li>
            <li>
                <?php
                // Fetch employee profile picture (reuse logic from employee_dashboard.php)
                $profile_sql = "SELECT Profile_pic FROM employees WHERE ID = ?";
                $profile_stmt = $conn->prepare($profile_sql);
                $profile_stmt->execute([$_SESSION['employee_id']]);
                $profile_data = $profile_stmt->fetch(PDO::FETCH_ASSOC);
                $has_profile_pic = (!empty($profile_data['Profile_pic']) && file_exists($profile_data['Profile_pic']));
                $profile_pic_path = $has_profile_pic ? $profile_data['Profile_pic'] : '';
                ?>
                <?php if ($has_profile_pic): ?>
                    <img src="<?= htmlspecialchars($profile_pic_path) ?>" class="profile-img" alt="Profile Pic">
                <?php else: ?>
                    <svg width="40" height="40" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" class="profile-img" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                        <rect width="120" height="120" fill="#197b88" rx="60"/>
                        <g fill="#ffffff" opacity="0.8">
                            <circle cx="60" cy="40" r="18"/>
                            <path d="M30 100 C30 85, 42 75, 60 75 C78 75, 90 85, 90 100 L30 100 Z"/>
                        </g>
                    </svg>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
</header>

<div class="container">
    <div class="job-card">
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
        <div class="section-title">Job Description</div>
        <div class="description-text">
            <?= nl2br(htmlspecialchars($job['Description'])) ?>
        </div>
        
        <?php if ($job['Special_requirements']): ?>
            <div class="section-title" style="margin-top: 24px;">Special Requirements</div>
            <div class="description-text">
                <?= nl2br(htmlspecialchars($job['Special_requirements'])) ?>
            </div>
        <?php endif; ?>
        <div class="job-actions">
            <a href="browse_jobs.php" class="btn">Back to Jobs</a>
            
            <?php if (!$job['has_applied']): ?>
                <a href="apply_job.php?job_id=<?= $job['ID'] ?>" class="btn btn-success">Apply Now</a>
            <?php else: ?>
                <button class="btn btn-disabled" disabled>Already Applied</button>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html> 