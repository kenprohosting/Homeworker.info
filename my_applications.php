<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: employee_login.php");
    exit();
}

require_once('db_connect.php');

// Handle application withdrawal
if (isset($_GET['action']) && $_GET['action'] === 'withdraw' && isset($_GET['application_id'])) {
    $application_id = $_GET['application_id'];
    
    // Verify the application belongs to this employee
    $verify_stmt = $conn->prepare("SELECT ID FROM job_applications WHERE ID = ? AND Employee_ID = ?");
    $verify_stmt->execute([$application_id, $_SESSION['employee_id']]);
    
    if ($verify_stmt->fetch()) {
        $stmt = $conn->prepare("UPDATE job_applications SET Status = 'withdrawn' WHERE ID = ?");
        $stmt->execute([$application_id]);
        header("Location: my_applications.php");
        exit();
    }
}

// Fetch all applications for this employee
$stmt = $conn->prepare("
    SELECT ja.*, j.Title as job_title, j.Location as job_location, j.Job_type, j.Salary_min, j.Salary_max,
           e.Name as employer_name, e.Contact as employer_contact
    FROM job_applications ja 
    JOIN jobs j ON ja.Job_ID = j.ID
    JOIN employer e ON j.Employer_ID = e.ID
    WHERE ja.Employee_ID = ?
    ORDER BY ja.Applied_at DESC
");
$stmt->execute([$_SESSION['employee_id']]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Applications - Houselp Connect</title>
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
        
        .stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .stat {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            min-width: 120px;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: rgb(24, 123, 136);
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            margin-top: 5px;
        }
        
        .applications-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 20px;
        }
        
        .application-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid rgb(24, 123, 136);
        }
        
        .application-card.accepted {
            border-left-color: #28a745;
        }
        
        .application-card.rejected {
            border-left-color: #dc3545;
        }
        
        .application-card.withdrawn {
            border-left-color: #6c757d;
            opacity: 0.7;
        }
        
        .application-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
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
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-accepted {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-withdrawn {
            background: #e9ecef;
            color: #495057;
        }
        
        .application-details {
            margin-bottom: 15px;
        }
        
        .application-details p {
            margin: 5px 0;
            color: #666;
        }
        
        .application-details strong {
            color: #333;
        }
        
        .salary {
            color: #28a745;
            font-weight: bold;
        }
        
        .cover-letter {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .application-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            background: rgb(24, 123, 136);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        
        .btn:hover {
            background: rgb(20, 100, 110);
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .no-applications {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .contact-info {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Houselp Connect</div>
    <nav>
        <a href="employee_dashboard.php">Dashboard</a>
        <a href="browse_jobs.php">Browse Jobs</a>
        <a href="employee_logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h1>My Job Applications</h1>
    
    <div class="stats">
        <div class="stat">
            <div class="stat-number"><?= count($applications) ?></div>
            <div class="stat-label">Total Applications</div>
        </div>
        <div class="stat">
            <div class="stat-number"><?= count(array_filter($applications, fn($a) => $a['Status'] === 'pending')) ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat">
            <div class="stat-number"><?= count(array_filter($applications, fn($a) => $a['Status'] === 'accepted')) ?></div>
            <div class="stat-label">Accepted</div>
        </div>
        <div class="stat">
            <div class="stat-number"><?= count(array_filter($applications, fn($a) => $a['Status'] === 'rejected')) ?></div>
            <div class="stat-label">Rejected</div>
        </div>
    </div>
    
    <?php if (empty($applications)): ?>
        <div class="no-applications">
            <h3>No applications yet</h3>
            <p>Start browsing jobs and apply to opportunities that match your skills!</p>
            <a href="browse_jobs.php" class="btn">Browse Jobs</a>
        </div>
    <?php else: ?>
        <div class="applications-grid">
            <?php foreach ($applications as $app): ?>
                <div class="application-card <?= $app['Status'] ?>">
                    <div class="application-header">
                        <div>
                            <h3 class="job-title"><?= htmlspecialchars($app['job_title']) ?></h3>
                            <p class="employer-name"><?= htmlspecialchars($app['employer_name']) ?></p>
                        </div>
                        <span class="status-badge status-<?= $app['Status'] ?>">
                            <?= ucfirst($app['Status']) ?>
                        </span>
                    </div>
                    
                    <div class="application-details">
                        <p><strong>Location:</strong> <?= htmlspecialchars($app['job_location']) ?></p>
                        <p><strong>Type:</strong> <?= ucfirst(str_replace('-', ' ', $app['Job_type'])) ?></p>
                        <?php if ($app['Salary_min'] || $app['Salary_max']): ?>
                            <p class="salary">
                                <strong>Salary:</strong> 
                                KSH <?= $app['Salary_min'] ? number_format($app['Salary_min']) : '0' ?> - 
                                <?= $app['Salary_max'] ? number_format($app['Salary_max']) : 'Negotiable' ?>
                            </p>
                        <?php endif; ?>
                        <p><strong>Applied:</strong> <?= date('M j, Y', strtotime($app['Applied_at'])) ?></p>
                    </div>
                    
                    <div class="cover-letter">
                        <strong>Your Cover Letter:</strong><br>
                        <?= nl2br(htmlspecialchars($app['Cover_letter'])) ?>
                    </div>
                    
                    <?php if ($app['Status'] === 'accepted'): ?>
                        <div class="contact-info">
                            <strong>Employer Contact:</strong> <?= htmlspecialchars($app['employer_contact']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="application-actions">
                        <?php if ($app['Status'] === 'pending'): ?>
                            <a href="?action=withdraw&application_id=<?= $app['ID'] ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Withdraw this application?')">Withdraw</a>
                        <?php elseif ($app['Status'] === 'accepted'): ?>
                            <span class="btn btn-secondary" style="cursor: default;">✓ Application Accepted</span>
                        <?php elseif ($app['Status'] === 'rejected'): ?>
                            <span class="btn btn-secondary" style="cursor: default;">✗ Application Rejected</span>
                        <?php elseif ($app['Status'] === 'withdrawn'): ?>
                            <span class="btn btn-secondary" style="cursor: default;">↶ Application Withdrawn</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html> 