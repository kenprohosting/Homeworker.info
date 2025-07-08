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
    <title>Browse Jobs - Houselp Connect</title>
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
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        h1 {
            color: rgb(24, 123, 136);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        input, select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .btn {
            background: rgb(24, 123, 136);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
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
        
        .job-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .job-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid rgb(24, 123, 136);
            transition: transform 0.2s;
        }
        
        .job-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .job-header {
            margin-bottom: 15px;
        }
        
        .job-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin: 0 0 5px 0;
        }
        
        .employer-name {
            color: #666;
            font-size: 14px;
        }
        
        .job-details {
            margin-bottom: 15px;
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
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .job-actions {
            display: flex;
            gap: 10px;
        }
        
        .applied-badge {
            background: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            display: inline-block;
        }
        
        .no-jobs {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .clear-filters {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Houselp Connect</div>
    <nav>
        <a href="employee_dashboard.php">Dashboard</a>
        <a href="my_applications.php">My Applications</a>
        <a href="employee_logout.php">Logout</a>
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

</body>
</html> 