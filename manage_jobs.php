<?php
session_start();
if (!isset($_SESSION['employer_id'])) {
    header("Location: employer_login.php");
    exit();
}

require_once('db_connect.php');

$message = '';

// Handle job status updates
if (isset($_GET['action']) && isset($_GET['job_id'])) {
    $job_id = $_GET['job_id'];
    $action = $_GET['action'];
    
    // Verify the job belongs to this employer
    $verify_stmt = $conn->prepare("SELECT ID FROM jobs WHERE ID = ? AND Employer_ID = ?");
    $verify_stmt->execute([$job_id, $_SESSION['employer_id']]);
    
    if ($verify_stmt->fetch()) {
        switch ($action) {
            case 'expire':
                $stmt = $conn->prepare("UPDATE jobs SET Status = 'expired' WHERE ID = ?");
                $stmt->execute([$job_id]);
                $message = '<div style="color: green; margin: 10px 0;">Job marked as expired.</div>';
                break;
            case 'cancel':
                $stmt = $conn->prepare("UPDATE jobs SET Status = 'cancelled' WHERE ID = ?");
                $stmt->execute([$job_id]);
                $message = '<div style="color: green; margin: 10px 0;">Job cancelled successfully.</div>';
                break;
            case 'fill':
                $stmt = $conn->prepare("UPDATE jobs SET Status = 'filled' WHERE ID = ?");
                $stmt->execute([$job_id]);
                $message = '<div style="color: green; margin: 10px 0;">Job marked as filled.</div>';
                break;
            case 'reactivate':
                $stmt = $conn->prepare("UPDATE jobs SET Status = 'active' WHERE ID = ?");
                $stmt->execute([$job_id]);
                $message = '<div style="color: green; margin: 10px 0;">Job reactivated successfully.</div>';
                break;
        }
    }
}

// Auto-expire jobs that have passed their expiry date
$conn->prepare("UPDATE jobs SET Status = 'expired' WHERE Expiry_date < CURDATE() AND Status = 'active'")->execute();

// Fetch all jobs for this employer
$stmt = $conn->prepare("
    SELECT j.*, 
           COUNT(ja.ID) as application_count,
           CASE 
               WHEN j.Expiry_date < CURDATE() THEN 'expired'
               ELSE j.Status 
           END as current_status
    FROM jobs j 
    LEFT JOIN job_applications ja ON j.ID = ja.Job_ID AND ja.Status = 'pending'
    WHERE j.Employer_ID = ?
    GROUP BY j.ID
    ORDER BY j.Created_at DESC
");
$stmt->execute([$_SESSION['employer_id']]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Jobs - Homeworker Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        h1 {
            color: rgb(24, 123, 136);
            margin: 0;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
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
        }
        
        .job-card.expired {
            border-left-color: #6c757d;
            opacity: 0.7;
        }
        
        .job-card.filled {
            border-left-color: #28a745;
        }
        
        .job-card.cancelled {
            border-left-color: #dc3545;
        }
        
        .job-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .job-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-expired {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-filled {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-cancelled {
            background: #f5c6cb;
            color: #721c24;
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
        
        .job-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .application-count {
            background: #e9ecef;
            color: #495057;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-bottom: 10px;
            display: inline-block;
        }
        
        .no-jobs {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .user-greeting {
            color: white;
            font-weight: 500;
            padding: 10px 16px;
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
            <li><span class="user-greeting">Hello, <?= htmlspecialchars($_SESSION['employer_name']) ?></span></li>
            <li><a class="nav-btn" href="employer_dashboard.php">Dashboard</a></li>
            <li><a class="nav-btn" href="post_job.php">Post Job</a></li>
            <li><a class="nav-btn" href="manage_jobs.php">My Jobs</a></li>
            <li><a class="nav-btn" href="employer_bookings.php">My Bookings</a></li>
            <li><a class="nav-btn" href="employer_logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<div class="container">
    <div class="header-actions">
        <h1>Manage My Jobs</h1>
        <a href="post_job.php" class="btn">Post New Job</a>
    </div>
    
    <?php echo $message; ?>
    
    <?php if (empty($jobs)): ?>
        <div class="no-jobs">
            <h3>No jobs posted yet</h3>
            <p>Start by posting your first job opportunity!</p>
            <a href="post_job.php" class="btn">Post Your First Job</a>
        </div>
    <?php else: ?>
        <div class="job-grid">
            <?php foreach ($jobs as $job): ?>
                <div class="job-card <?= $job['current_status'] ?>">
                    <div class="job-header">
                        <h3 class="job-title"><?= htmlspecialchars($job['Title']) ?></h3>
                        <span class="status-badge status-<?= $job['current_status'] ?>">
                            <?= ucfirst($job['current_status']) ?>
                        </span>
                    </div>
                    
                    <div class="application-count">
                        <?= $job['application_count'] ?> application<?= $job['application_count'] != 1 ? 's' : '' ?>
                    </div>
                    
                    <div class="job-details">
                        <p><strong>Location:</strong> <?= htmlspecialchars($job['Location']) ?></p>
                        <p><strong>Type:</strong> <?= ucfirst(str_replace('-', ' ', $job['Job_type'])) ?></p>
                        <p><strong>Start Date:</strong> <?= date('M j, Y', strtotime($job['Start_date'])) ?></p>
                        <p><strong>Expires:</strong> <?= date('M j, Y', strtotime($job['Expiry_date'])) ?></p>
                        <?php if ($job['Salary_min'] || $job['Salary_max']): ?>
                            <p><strong>Salary:</strong> 
                                KSH <?= $job['Salary_min'] ? number_format($job['Salary_min']) : '0' ?> - 
                                <?= $job['Salary_max'] ? number_format($job['Salary_max']) : 'Negotiable' ?>
                            </p>
                        <?php endif; ?>
                        <p><strong>Posted:</strong> <?= date('M j, Y', strtotime($job['Created_at'])) ?></p>
                    </div>
                    
                    <div class="job-actions">
                        <?php if ($job['current_status'] === 'active'): ?>
                            <a href="view_applications.php?job_id=<?= $job['ID'] ?>" class="btn">View Applications</a>
                            <a href="?action=fill&job_id=<?= $job['ID'] ?>" class="btn btn-success" 
                               onclick="return confirm('Mark this job as filled?')">Mark Filled</a>
                            <a href="?action=cancel&job_id=<?= $job['ID'] ?>" class="btn btn-danger" 
                               onclick="return confirm('Cancel this job?')">Cancel</a>
                        <?php elseif ($job['current_status'] === 'expired'): ?>
                            <a href="?action=reactivate&job_id=<?= $job['ID'] ?>" class="btn btn-warning">Reactivate</a>
                            <a href="?action=cancel&job_id=<?= $job['ID'] ?>" class="btn btn-danger" 
                               onclick="return confirm('Cancel this job?')">Cancel</a>
                        <?php elseif ($job['current_status'] === 'filled'): ?>
                            <a href="view_applications.php?job_id=<?= $job['ID'] ?>" class="btn">View Applications</a>
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

<script>
// Hamburger menu toggle
const hamburgerBtn = document.getElementById('hamburgerBtn');
const navLinks = document.getElementById('mainNav');
hamburgerBtn.addEventListener('click', function() {
  const expanded = hamburgerBtn.getAttribute('aria-expanded') === 'true';
  hamburgerBtn.setAttribute('aria-expanded', !expanded);
  navLinks.classList.toggle('show');
});
// Close menu when clicking outside (mobile only)
document.addEventListener('click', function(e) {
  if (window.innerWidth <= 900 && navLinks.classList.contains('show')) {
    if (!navLinks.contains(e.target) && e.target !== hamburgerBtn && !hamburgerBtn.contains(e.target)) {
      navLinks.classList.remove('show');
      hamburgerBtn.setAttribute('aria-expanded', 'false');
    }
  }
});
</script>
</body>
</html> 