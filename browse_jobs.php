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
    <title>Browse Jobs - Homeworker Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <a href="javascript:history.back()" class="back-btn">← Back</a>

<header>
    <div class="logo">
        <img src="bghse.png" alt="Logo" style="height: 40px;">
    </div>
    <nav class="main-nav">
        <ul class="nav-links">
            <li><a class="nav-btn" href="employee_dashboard.php">Dashboard</a></li>
            <li><a class="nav-btn" href="my_applications.php">My Applications</a></li>
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