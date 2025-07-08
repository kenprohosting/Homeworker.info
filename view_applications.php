<?php
session_start();
if (!isset($_SESSION['employer_id'])) {
    header("Location: employer_login.php");
    exit();
}

require_once('db_connect.php');

$message = '';

if (!isset($_GET['job_id'])) {
    header("Location: manage_jobs.php");
    exit();
}

$job_id = $_GET['job_id'];

// Verify the job belongs to this employer
$verify_stmt = $conn->prepare("SELECT ID, Title FROM jobs WHERE ID = ? AND Employer_ID = ?");
$verify_stmt->execute([$job_id, $_SESSION['employer_id']]);
$job = $verify_stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header("Location: manage_jobs.php");
    exit();
}

// Handle application status updates
if (isset($_GET['action']) && isset($_GET['application_id'])) {
    $application_id = $_GET['application_id'];
    $action = $_GET['action'];
    
    // Verify the application belongs to this employer's job
    $verify_app_stmt = $conn->prepare("
        SELECT ja.ID FROM job_applications ja 
        JOIN jobs j ON ja.Job_ID = j.ID 
        WHERE ja.ID = ? AND j.Employer_ID = ?
    ");
    $verify_app_stmt->execute([$application_id, $_SESSION['employer_id']]);
    
    if ($verify_app_stmt->fetch()) {
        switch ($action) {
            case 'accept':
                $stmt = $conn->prepare("UPDATE job_applications SET Status = 'accepted' WHERE ID = ?");
                $stmt->execute([$application_id]);
                $message = '<div style="color: green; margin: 10px 0;">Application accepted successfully.</div>';
                break;
            case 'reject':
                $stmt = $conn->prepare("UPDATE job_applications SET Status = 'rejected' WHERE ID = ?");
                $stmt->execute([$application_id]);
                $message = '<div style="color: green; margin: 10px 0;">Application rejected.</div>';
                break;
        }
    }
}

// Fetch all applications for this job
$stmt = $conn->prepare("
    SELECT ja.*, emp.Name as employee_name, emp.Age, emp.Skills, emp.Location, 
           emp.Education_level, emp.Language, emp.Contact, emp.email,
           emp.profile_pic
    FROM job_applications ja 
    JOIN employee emp ON ja.Employee_ID = emp.ID
    WHERE ja.Job_ID = ?
    ORDER BY ja.Applied_at DESC
");
$stmt->execute([$job_id]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Applications - Houselp Connect</title>
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
        
        .header-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: rgb(24, 123, 136);
            margin: 0 0 10px 0;
        }
        
        .job-title {
            font-size: 18px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .stats {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }
        
        .stat {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: rgb(24, 123, 136);
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        
        .btn {
            background: rgb(24, 123, 136);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
            font-size: 14px;
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
        
        .application-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .profile-pic {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        
        .employee-info h3 {
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .employee-info p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: auto;
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
        <a href="employer_dashboard.php">Dashboard</a>
        <a href="manage_jobs.php">My Jobs</a>
        <a href="employer_logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <div class="header-section">
        <h1>Job Applications</h1>
        <p class="job-title"><?= htmlspecialchars($job['Title']) ?></p>
        
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
    </div>
    
    <?php echo $message; ?>
    
    <?php if (empty($applications)): ?>
        <div class="no-applications">
            <h3>No applications yet</h3>
            <p>Applications will appear here once employees apply for this job.</p>
        </div>
    <?php else: ?>
        <div class="applications-grid">
            <?php foreach ($applications as $app): ?>
                <div class="application-card <?= $app['Status'] ?>">
                    <div class="application-header">
                        <?php
                            $profile = 'uploads/default.jpg';
                            if (!empty($app['profile_pic'])) {
                                if (file_exists(__DIR__ . '/' . $app['profile_pic'])) {
                                    $profile = $app['profile_pic'];
                                }
                            }
                        ?>
                        <img src="<?= htmlspecialchars($profile) ?>" alt="Profile Picture" class="profile-pic">
                        <div class="employee-info">
                            <h3><?= htmlspecialchars($app['employee_name']) ?> (<?= $app['Age'] ?>)</h3>
                            <p>Applied <?= date('M j, Y', strtotime($app['Applied_at'])) ?></p>
                        </div>
                        <span class="status-badge status-<?= $app['Status'] ?>">
                            <?= ucfirst($app['Status']) ?>
                        </span>
                    </div>
                    
                    <div class="application-details">
                        <p><strong>Skills:</strong> <?= htmlspecialchars($app['Skills']) ?></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($app['Location']) ?></p>
                        <p><strong>Education:</strong> <?= htmlspecialchars($app['Education_level']) ?></p>
                        <p><strong>Languages:</strong> <?= htmlspecialchars($app['Language']) ?></p>
                    </div>
                    
                    <div class="cover-letter">
                        <strong>Cover Letter:</strong><br>
                        <?= nl2br(htmlspecialchars($app['Cover_letter'])) ?>
                    </div>
                    
                    <div class="contact-info">
                        <strong>Contact:</strong> <?= htmlspecialchars($app['Contact']) ?><br>
                        <strong>Email:</strong> <?= htmlspecialchars($app['email']) ?>
                    </div>
                    
                    <?php if ($app['Status'] === 'pending'): ?>
                        <div class="application-actions">
                            <a href="?job_id=<?= $job_id ?>&action=accept&application_id=<?= $app['ID'] ?>" 
                               class="btn btn-success" 
                               onclick="return confirm('Accept this application?')">Accept</a>
                            <a href="?job_id=<?= $job_id ?>&action=reject&application_id=<?= $app['ID'] ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Reject this application?')">Reject</a>
                        </div>
                    <?php elseif ($app['Status'] === 'accepted'): ?>
                        <div class="application-actions">
                            <span class="btn btn-secondary" style="cursor: default;">Application Accepted</span>
                        </div>
                    <?php elseif ($app['Status'] === 'rejected'): ?>
                        <div class="application-actions">
                            <span class="btn btn-secondary" style="cursor: default;">Application Rejected</span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="manage_jobs.php" class="btn">Back to My Jobs</a>
    </div>
</div>

</body>
</html> 