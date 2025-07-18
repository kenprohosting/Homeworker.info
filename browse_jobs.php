<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: employee_login.php");
    exit();
}

require_once('db_connect.php');

// Build filter query
$filter_sql = "
    SELECT j.*, e.Name as employer_name, e.Location as employer_location,
           CASE WHEN ja.ID IS NOT NULL THEN 1 ELSE 0 END as has_applied
    FROM jobs j 
    JOIN employer e ON j.Employer_ID = e.ID
    LEFT JOIN job_applications ja ON j.ID = ja.Job_ID AND ja.Employee_ID = ?
    WHERE j.Status = 'active' AND j.Expiry_date >= CURDATE()
";
$params = [$_SESSION['employee_id']];

if (!empty($_GET['location'])) {
    $filter_sql .= " AND j.Location LIKE ?";
    $params[] = '%' . $_GET['location'] . '%';
}

if (!empty($_GET['skills'])) {
    $filter_sql .= " AND j.Required_skills LIKE ?";
    $params[] = '%' . $_GET['skills'] . '%';
}

if (!empty($_GET['job_type'])) {
    $filter_sql .= " AND j.Job_type = ?";
    $params[] = $_GET['job_type'];
}

if (!empty($_GET['salary_min'])) {
    $filter_sql .= " AND (j.Salary_max >= ? OR j.Salary_max IS NULL)";
    $params[] = $_GET['salary_min'];
}

$filter_sql .= " ORDER BY j.Created_at DESC";

$stmt = $conn->prepare($filter_sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="favicon.png">
    <title>Browse Jobs - Homeworker Connect</title>
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
        
        /* Page content styling */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
            flex: 1;
        }
        
        h1 {
            color: #197b88;
            font-size: 2rem;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }
        
        /* Filter section styling */
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            border-color: #197b88;
            outline: none;
            box-shadow: 0 0 0 2px rgba(25, 123, 136, 0.1);
        }
        
        .btn {
            background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: linear-gradient(90deg, #17606e 0%, #1ec8c8 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(24,123,136,0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-success {
            background: linear-gradient(90deg, #28a745 0%, #34ce57 100%);
        }
        
        .btn-success:hover {
            background: linear-gradient(90deg, #218838 0%, #28a745 100%);
        }
        
        .btn-disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .btn-disabled:hover {
            transform: none;
            box-shadow: none;
        }
        
        /* Job grid styling */
        .job-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 20px;
        }
        
        .job-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .job-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .applied-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .job-title {
            color: #197b88;
            font-size: 1.3rem;
            margin: 0 0 5px 0;
            font-weight: 600;
        }
        
        .employer-name {
            color: #666;
            font-size: 14px;
            margin: 0 0 15px 0;
        }
        
        .job-details p {
            margin: 8px 0;
            color: #555;
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
            margin: 15px 0;
            font-size: 14px;
            line-height: 1.5;
            color: #555;
        }
        
        .job-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .no-jobs {
            text-align: center;
            padding: 50px;
            color: #666;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .clear-filters {
            text-align: center;
            margin-top: 15px;
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
            
            .filter-form {
                grid-template-columns: 1fr;
            }
            
            .job-grid {
                grid-template-columns: 1fr;
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
            <li><a class="nav-btn" href="my_applications.php">My Applications</a></li>
            <li><a class="nav-btn" href="employee_profile.php">Update Profile</a></li>
            <li><a class="nav-btn" href="employee_logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<div class="container">
    <h1>Browse Available Jobs</h1>
    
    <div class="filter-section">
        <form method="GET" class="filter-form">
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" 
                       value="<?= htmlspecialchars($_GET['location'] ?? '') ?>" 
                       placeholder="e.g., Nairobi, Westlands">
            </div>
            
            <div class="form-group">
                <label for="skills">Skills</label>
                <input type="text" id="skills" name="skills" 
                       value="<?= htmlspecialchars($_GET['skills'] ?? '') ?>" 
                       placeholder="e.g., Cleaning, Cooking">
            </div>
            
            <div class="form-group">
                <label for="job_type">Job Type</label>
                <select id="job_type" name="job_type">
                    <option value="">All Types</option>
                    <option value="one-time" <?= ($_GET['job_type'] ?? '') === 'one-time' ? 'selected' : '' ?>>One-time</option>
                    <option value="part-time" <?= ($_GET['job_type'] ?? '') === 'part-time' ? 'selected' : '' ?>>Part-time</option>
                    <option value="full-time" <?= ($_GET['job_type'] ?? '') === 'full-time' ? 'selected' : '' ?>>Full-time</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="salary_min">Minimum Salary (KSH)</label>
                <input type="number" id="salary_min" name="salary_min" 
                       value="<?= htmlspecialchars($_GET['salary_min'] ?? '') ?>" 
                       placeholder="e.g., 5000" min="0">
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Filter Jobs</button>
            </div>
        </form>
        
        <?php if (!empty($_GET)): ?>
            <div class="clear-filters">
                <a href="browse_jobs.php" class="btn btn-secondary">Clear Filters</a>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (empty($jobs)): ?>
        <div class="no-jobs">
            <h3>No jobs found</h3>
            <p>Try adjusting your filters or check back later for new opportunities.</p>
        </div>
    <?php else: ?>
        <div class="job-grid">
            <?php foreach ($jobs as $job): ?>
                <div class="job-card">
                    <?php if ($job['has_applied']): ?>
                        <div class="applied-badge">✓ Applied</div>
                    <?php endif; ?>
                    
                    <div class="job-header">
                        <h3 class="job-title"><?= htmlspecialchars($job['Title']) ?></h3>
                        <p class="employer-name">Posted by <?= htmlspecialchars($job['employer_name']) ?></p>
                    </div>
                    
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
                        <?= htmlspecialchars(substr($job['Description'], 0, 150)) ?>
                        <?= strlen($job['Description']) > 150 ? '...' : '' ?>
                    </div>
                    
                    <?php if ($job['Special_requirements']): ?>
                        <p><strong>Special Requirements:</strong> <?= htmlspecialchars($job['Special_requirements']) ?></p>
                    <?php endif; ?>
                    
                    <div class="job-actions">
                        <a href="view_job.php?id=<?= $job['ID'] ?>" class="btn">View Details</a>
                        <?php if (!$job['has_applied']): ?>
                            <a href="apply_job.php?job_id=<?= $job['ID'] ?>" class="btn btn-success">Apply Now</a>
                        <?php else: ?>
                            <button class="btn btn-disabled" disabled>Already Applied</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
</footer>

</body>
</html> 