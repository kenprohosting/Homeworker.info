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
    <link rel="icon" type="image/png" href="/favicon.png">
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
        
        /* Override main styles.css navigation */
        header .nav-links li a,
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
        
        header .nav-links li a:hover,
        header .nav-links li a:focus,
        .nav-btn:hover, 
        .nav-btn:focus {
            background: linear-gradient(90deg, #17606e 0%, #1ec8c8 100%) !important;
            color: #ffd700 !important;
            box-shadow: 0 4px 16px rgba(24,123,136,0.16) !important;
            outline: none !important;
            border-color: #125a66 !important;
        }
        
        /* Ensure nav links don't inherit styles from index page */
        header .nav-links li a.nav-btn {
            background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%) !important;
            color: #fff !important;
            text-decoration: none !important;
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
        
        /* Override any styles from the landing page */
        header .nav-links li a {
            color: #fff !important;
            text-decoration: none !important;
            background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%) !important;
            padding: 10px 22px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            margin: 0 6px !important;
            transition: background 0.2s !important;
        }
        
        header .nav-links li a:hover {
            background: linear-gradient(90deg, #17606e 0%, #1ec8c8 100%) !important;
            color: #ffd700 !important;
        }
        
        /* Main content area styling */
        .form-container { 
            padding: 30px;
            width: 100vw;
            max-width: none;
            margin-left: calc(50% - 50vw);
            box-sizing: border-box;
            background: #f8f9fa;
            border: none !important;
            flex: 1;
        }
        
        /* Page title styling */
        h2 {
            color: #197b88;
            font-size: 2rem;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }
        
        /* Search form styling */
        .filter-form {
            display: flex;
            flex-wrap: nowrap;
            gap: 10px;
            align-items: center;
            margin-bottom: 30px;
            padding: 0;
        }
        
        .filter-form input, .filter-form select {
            padding: 10px 14px;
            border: 1px solid #ddd;
            border-radius: 5px;
            flex: 0 1 auto;
            width: 180px;
            font-size: 14px;
            outline: none;
            font-family: 'Segoe UI', sans-serif;
            height: 40px;
            box-sizing: border-box;
        }
        
        .filter-form input:focus, .filter-form select:focus {
            border-color: #197b88;
            box-shadow: 0 0 0 2px rgba(25, 123, 136, 0.1);
        }
        
        .filter-form button {
            padding: 10px 20px;
            background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            height: 40px;
            width: 100px;
            flex-shrink: 0;
            transition: all 0.3s ease;
            font-family: 'Segoe UI', sans-serif;
            box-sizing: border-box;
        }
        
        .filter-form button:hover {
            background: linear-gradient(90deg, #17606e 0%, #1ec8c8 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(24,123,136,0.3);
        }
        
        /* Job grid styling */
        .job-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 30px;
            margin-top: 30px;
            padding: 0 20px;
        }
        
        .job-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            text-align: left;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            position: relative;
        }
        
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .applied-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .job-title {
            color: #197b88;
            font-size: 1.4rem;
            margin-bottom: 5px;
            font-weight: 600;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .employer-name {
            color: #666;
            font-size: 1rem;
            margin-bottom: 15px;
        }
        
        .job-details p {
            font-size: 1rem;
            margin: 10px 0;
            line-height: 1.5;
            color: #555;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .job-details strong {
            color: #333;
            font-weight: 600;
        }
        
        .salary {
            color: #28a745;
            font-weight: bold;
        }
        
        .job-description {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-size: 0.95rem;
            line-height: 1.5;
            color: #555;
        }
        
        /* Button styling */
        .btn {
            margin-top: 20px;
            padding: 12px 25px;
            font-size: 1.1rem;
            font-weight: 600;
            background: linear-gradient(90deg, #197b88 0%, #1ec8c8 100%);
            border: none;
            border-radius: 25px;
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
            display: inline-block;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .btn:hover {
            background: linear-gradient(90deg, #17606e 0%, #1ec8c8 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(24,123,136,0.3);
            color: white;
            text-decoration: none;
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
            margin: 20px;
        }
        
        .clear-filters {
            text-align: center;
            margin-top: 15px;
        }
        
        /* Fix for mobile responsiveness */
        @media (max-width: 900px) {
            .job-grid {
                grid-template-columns: 1fr;
                padding: 0 5px;
            }
            .form-container {
                padding: 16px 2vw;
            }
            .job-card {
                padding: 18px 6px;
            }
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
            .form-container {
                padding: 10px 2vw;
                width: 100vw;
                margin-left: 0;
            }
            .filter-form {
                flex-wrap: wrap;
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }
            .filter-form input, .filter-form select, .filter-form button {
                width: 100%;
                min-width: 0;
                box-sizing: border-box;
            }
            .job-grid {
                grid-template-columns: 1fr;
                gap: 18px;
                padding: 0 2px;
            }
            .job-card {
                padding: 14px 4px;
            }
            h2 {
                font-size: 1.3rem;
                margin-bottom: 18px;
            }
        }
        
        @media (max-width: 480px) {
            .form-container {
                padding: 4px 1vw;
            }
            .job-card {
                padding: 8px 2px;
            }
            h2 {
                font-size: 1.1rem;
            }
            .filter-form input, .filter-form select, .filter-form button {
                font-size: 13px;
                height: 36px;
                padding: 7px 8px;
            }
        }
        
        /* Hamburger menu for mobile */
        .hamburger {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 44px;
            height: 44px;
            background: none;
            border: none;
            cursor: pointer;
            margin-left: auto;
            z-index: 1002;
        }
        
        .hamburger .bar {
            width: 28px;
            height: 3px;
            background: #fff;
            margin: 4px 0;
            border-radius: 2px;
            transition: 0.3s;
            display: block;
        }
        
        @media (max-width: 900px) {
            .hamburger {
                display: flex;
            }
            .main-nav {
                width: 100%;
            }
            .nav-links {
                display: none !important;
                flex-direction: column;
                position: absolute;
                top: 60px;
                right: 0;
                left: 0;
                background: #0A4A70;
                z-index: 1001;
                box-shadow: 0 8px 24px rgba(0,0,0,0.08);
                padding: 18px 0 10px 0;
                border-radius: 0 0 12px 12px;
                gap: 0;
                margin: 0;
                width: 100vw;
                min-width: 0;
            }
            .nav-links.show {
                display: flex !important;
            }
            .nav-links li {
                width: 100%;
                text-align: center;
                margin: 0;
                padding: 0;
            }
            .nav-links li a, .nav-links li span {
                display: block;
                width: 100%;
                padding: 14px 0;
                margin: 0;
                border-radius: 0;
                border-bottom: 1px solid #197b88;
            }
            .nav-links li:last-child a {
                border-bottom: none;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <img src="bghse.png" alt="Logo" style="height: 40px;">
    </div>
    <button class="hamburger" id="hamburgerBtn" aria-label="Open navigation" aria-expanded="false" aria-controls="mainNav" type="button">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
    </button>
    <nav class="main-nav">
        <ul class="nav-links" id="mainNav">
            <li><span class="user-greeting">Hello, <?= htmlspecialchars($_SESSION['employee_name']) ?></span></li>
            <li><a class="nav-btn" href="employee_dashboard.php">Dashboard</a></li>
            <li><a class="nav-btn" href="browse_jobs.php">Browse Jobs</a></li>
            <li><a class="nav-btn" href="my_applications.php">My Applications</a></li>
            <li><a class="nav-btn" href="employee_profile.php">Update Profile</a></li>
            <li><a class="nav-btn" href="employee_logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<div class="form-container">
    <h2>Browse Available Jobs</h2>
    
    <form method="GET" class="filter-form">
        <input type="text" name="location" value="<?= htmlspecialchars($_GET['location'] ?? '') ?>" placeholder="Location (e.g., Nairobi)">
        <input type="text" name="skills" value="<?= htmlspecialchars($_GET['skills'] ?? '') ?>" placeholder="Skills (e.g., Cleaning)">
        <select name="job_type">
            <option value="">All Job Types</option>
            <option value="one-time" <?= ($_GET['job_type'] ?? '') === 'one-time' ? 'selected' : '' ?>>One-time</option>
            <option value="part-time" <?= ($_GET['job_type'] ?? '') === 'part-time' ? 'selected' : '' ?>>Part-time</option>
            <option value="full-time" <?= ($_GET['job_type'] ?? '') === 'full-time' ? 'selected' : '' ?>>Full-time</option>
        </select>
        <input type="number" name="salary_min" value="<?= htmlspecialchars($_GET['salary_min'] ?? '') ?>" placeholder="Min Salary (KSH)" min="0">
        <button type="submit" class="btn search-btn">Search</button>
        <?php if (!empty($_GET)): ?>
            <a href="browse_jobs.php" style="height:40px;display:flex;align-items:center;justify-content:center;background:#6c757d;color:white;border-radius:5px;padding:0 15px;text-decoration:none;font-size:14px;">Clear</a>
        <?php endif; ?>
    </form>
    
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
                    
                    <h3 class="job-title"><?= htmlspecialchars($job['Title']) ?></h3>
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
    <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p> | <a href="privacy_policy.php" style="text-decoration: none; color: inherit;">Privacy Policy</a>
</footer>

<script>
    // Hamburger menu functionality
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const mainNav = document.getElementById('mainNav');
    
    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', function() {
            mainNav.classList.toggle('show');
            const isExpanded = mainNav.classList.contains('show');
            hamburgerBtn.setAttribute('aria-expanded', isExpanded);
        });
    }
</script>

</body>
</html>