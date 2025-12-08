<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['admin_name'];

// Date filter
$start = $_GET['start'] ?? '';
$end   = $_GET['end'] ?? '';
$agent_id = $_GET['agent_id'] ?? '';
$code_status = $_GET['code_status'] ?? '';

// Build WHERE clause for agents
$agents_where = "1=1";
$agents_params = [];

// Build WHERE clause for codes
$codes_where = "1=1";
$codes_params = [];

if ($start && $end) {
    $agents_where .= " AND DATE(created_at) BETWEEN :astart AND :aend";
    $agents_params[':astart'] = $start;
    $agents_params[':aend'] = $end;
    
    $codes_where .= " AND DATE(created_at) BETWEEN :cstart AND :cend";
    $codes_params[':cstart'] = $start;
    $codes_params[':cend'] = $end;
}

if ($agent_id) {
    $codes_where .= " AND agent_id = :agent_id";
    $codes_params[':agent_id'] = $agent_id;
}

if ($code_status && $code_status != 'all') {
    $codes_where .= " AND status = :status";
    $codes_params[':status'] = $code_status;
}

// 1. AGENT STATS
$agent_stats = [];

// Total agents
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM agents WHERE DATE(created_at) BETWEEN :start AND :end");
$stmt->execute([':start' => $start ?: '1970-01-01', ':end' => $end ?: date('Y-m-d')]);
$agent_stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Active/Inactive agents (if you have is_active field)
try {
    $stmt = $conn->prepare("SELECT 
        SUM(is_active=1) as active,
        SUM(is_active=0) as inactive
        FROM agents WHERE DATE(created_at) BETWEEN :start AND :end");
    $stmt->execute([':start' => $start ?: '1970-01-01', ':end' => $end ?: date('Y-m-d')]);
    $agent_activity = $stmt->fetch(PDO::FETCH_ASSOC);
    $agent_stats['active'] = $agent_activity['active'] ?? 0;
    $agent_stats['inactive'] = $agent_activity['inactive'] ?? 0;
} catch(Exception $e) {
    // Field might not exist
    $agent_stats['active'] = $agent_stats['total'];
    $agent_stats['inactive'] = 0;
}

// 2. CODE STATS
$code_stats = [];

// Codes summary
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total,
    SUM(status='active') as active,
    SUM(status='used') as used,
    SUM(status='expired') as expired,
    MIN(created_at) as first_code_date,
    MAX(created_at) as last_code_date
    FROM agent_registration_codes WHERE DATE(created_at) BETWEEN :start AND :end");
$stmt->execute([':start' => $start ?: '1970-01-01', ':end' => $end ?: date('Y-m-d')]);
$code_stats['summary'] = $stmt->fetch(PDO::FETCH_ASSOC);

// Codes by agent (top 10)
$stmt = $conn->prepare("SELECT a.name, COUNT(c.id) as code_count 
    FROM agent_registration_codes c 
    LEFT JOIN agents a ON c.agent_id = a.id 
    WHERE DATE(c.created_at) BETWEEN :start AND :end 
    GROUP BY c.agent_id 
    ORDER BY code_count DESC 
    LIMIT 10");
$stmt->execute([':start' => $start ?: '1970-01-01', ':end' => $end ?: date('Y-m-d')]);
$code_stats['by_agent'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. GET AGENTS FOR DROPDOWN
$stmt = $conn->prepare("SELECT id, name FROM agents ORDER BY name");
$stmt->execute();
$agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. EXPORT FUNCTIONALITY
if (isset($_GET['export'])) {
    $export_type = $_GET['export_type'] ?? 'summary';
    
    if ($export_type == 'agents') {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=agents_report_$start-$end.csv");
        
        $output = fopen("php://output", "w");
        
        // Get detailed agent data
        $stmt = $conn->prepare("SELECT 
            id, 
            name, 
            email, 
            phone,
            created_at,
            last_login
            FROM agents 
            WHERE DATE(created_at) BETWEEN :start AND :end 
            ORDER BY created_at DESC");
        $stmt->execute([':start' => $start, ':end' => $end]);
        $agents_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Write headers
        fputcsv($output, ["Agent ID", "Name", "Email", "Phone", "Created At", "Last Login"]);
        
        // Write data
        foreach ($agents_data as $agent) {
            fputcsv($output, [
                $agent['id'],
                $agent['name'],
                $agent['email'],
                $agent['phone'] ?? '',
                $agent['created_at'],
                $agent['last_login'] ?? ''
            ]);
        }
        
        fclose($output);
        exit();
        
    } elseif ($export_type == 'codes') {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=codes_report_$start-$end.csv");
        
        $output = fopen("php://output", "w");
        
        // Get detailed code data
        $stmt = $conn->prepare("SELECT 
            c.id,
            c.code,
            c.status,
            c.created_at,
            c.used_at,
            a.name as agent_name,
            c.agent_id
            FROM agent_registration_codes c 
            LEFT JOIN agents a ON c.agent_id = a.id 
            WHERE DATE(c.created_at) BETWEEN :start AND :end 
            ORDER BY c.created_at DESC");
        $stmt->execute([':start' => $start, ':end' => $end]);
        $codes_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Write headers
        fputcsv($output, ["Code ID", "Code", "Status", "Created At", "Used At", "Agent Name", "Agent ID"]);
        
        // Write data
        foreach ($codes_data as $code) {
            fputcsv($output, [
                $code['id'],
                $code['code'],
                $code['status'],
                $code['created_at'],
                $code['used_at'] ?? '',
                $code['agent_name'] ?? 'Unassigned',
                $code['agent_id'] ?? ''
            ]);
        }
        
        fclose($output);
        exit();
        
    } else {
        // Summary export
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=summary_report_$start-$end.csv");
        
        $output = fopen("php://output", "w");
        fputcsv($output, ["Type", "Count"]);
        fputcsv($output, ["Total Agents", $agent_stats['total']]);
        fputcsv($output, ["Active Agents", $agent_stats['active']]);
        fputcsv($output, ["Inactive Agents", $agent_stats['inactive']]);
        fputcsv($output, ["Total Codes", $code_stats['summary']['total'] ?? 0]);
        fputcsv($output, ["Active Codes", $code_stats['summary']['active'] ?? 0]);
        fputcsv($output, ["Used Codes", $code_stats['summary']['used'] ?? 0]);
        fputcsv($output, ["Expired Codes", $code_stats['summary']['expired'] ?? 0]);
        
        fclose($output);
        exit();
    }
}

// Get recent agents for display
$stmt = $conn->prepare("SELECT id, name, email, created_at FROM agents ORDER BY created_at DESC LIMIT 10");
$stmt->execute();
$recent_agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta charset="UTF-8">
    <title>Reports - Homeworker Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .admin-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        .admin-nav {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .admin-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .admin-nav a {
            color: #2c3e50;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .admin-nav a:hover {
            background: #f8f9fa;
        }
        .admin-nav a.active {
            background: #3498db;
            color: white;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #3498db;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 10px;
        }
        .stat-label {
            color: #666;
            font-size: 1.1rem;
        }
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        .content-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .content-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .item-list {
            list-style: none;
            padding: 0;
        }
        .item-list li {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .item-list li:last-child {
            border-bottom: none;
        }
        .item-info h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }
        .item-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-used {
            background: #cce5ff;
            color: #004085;
        }
        .status-expired {
            background: #f8d7da;
            color: #721c24;
        }
        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .export-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .export-btn {
            background: white;
            border: 2px solid #3498db;
            color: #3498db;
            padding: 15px;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            font-weight: 600;
        }
        .export-btn:hover {
            background: #3498db;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        .export-btn.summary {
            border-color: #2ecc71;
            color: #2ecc71;
        }
        .export-btn.summary:hover {
            background: #2ecc71;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
        }
        .export-btn.agents {
            border-color: #9b59b6;
            color: #9b59b6;
        }
        .export-btn.agents:hover {
            background: #9b59b6;
            box-shadow: 0 4px 15px rgba(155, 89, 182, 0.3);
        }
        .export-btn.codes {
            border-color: #e74c3c;
            color: #e74c3c;
        }
        .export-btn.codes:hover {
            background: #e74c3c;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }
        .quick-stats {
            background: #e8f4fd;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        .quick-stats h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .date-range {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-weight: bold;
            color: #2c3e50;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            font-style: italic;
        }
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .percentage-bar {
            background: #ecf0f1;
            height: 10px;
            border-radius: 5px;
            margin-top: 5px;
            overflow: hidden;
        }
        .percentage-fill {
            height: 100%;
            background: #3498db;
            border-radius: 5px;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }
        .input-group .input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Reports Dashboard</h1>
            <p>Generate detailed statistics and export data in CSV format</p>
        </div>

        <div class="admin-nav">
            <ul>
                <li><a href="https://homeworker.info/" style="color: #e74c3c;">Back</a></li>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="manage_agents.php">Manage Agents</a></li>
                <li><a href="manage_employees.php">Manage Employees</a></li>
                <li><a href="manage_employers.php">Manage Employers</a></li>
                <li><a href="manage_codes.php">Registration Codes</a></li>
                <li><a href="reports.php" class="active">Reports</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="logout.php" style="color: #e74c3c;">Logout</a></li>
            </ul>
        </div>

        <div class="quick-stats">
            <h3>📊 Report Generator</h3>
            <p>Filter data by date range and export detailed reports. Select a date range to enable export options.</p>
            <?php if ($start && $end): ?>
                <div class="date-range">
                    📅 Date Range: <?= date('F j, Y', strtotime($start)) ?> to <?= date('F j, Y', strtotime($end)) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h3>Filter Options</h3>
            <form method="GET" class="filter-form">
                <div class="filter-grid">
                    <div class="input-group">
                        <label>Start Date</label>
                        <input type="date" name="start" class="input" value="<?= htmlspecialchars($start) ?>" required>
                    </div>

                    <div class="input-group">
                        <label>End Date</label>
                        <input type="date" name="end" class="input" value="<?= htmlspecialchars($end) ?>" required>
                    </div>

                    <div class="input-group">
                        <label>Agent</label>
                        <select name="agent_id" class="input">
                            <option value="">All Agents</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?= $agent['id'] ?>" <?= $agent_id == $agent['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($agent['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Code Status</label>
                        <select name="code_status" class="input">
                            <option value="all">All Statuses</option>
                            <option value="active" <?= $code_status == 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="used" <?= $code_status == 'used' ? 'selected' : '' ?>>Used</option>
                            <option value="expired" <?= $code_status == 'expired' ? 'selected' : '' ?>>Expired</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn">Apply Filters</button>
                    <button type="button" class="btn" onclick="window.location.href='reports.php'" style="background: #95a5a6;">Clear Filters</button>
                </div>
            </form>
        </div>

        <?php if ($start && $end): ?>
            <!-- Summary Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $agent_stats['total'] ?></div>
                    <div class="stat-label">Registered Agents</div>
                    <div style="margin-top: 10px; font-size: 0.9rem; color: #7f8c8d;">
                        Active: <?= $agent_stats['active'] ?> | Inactive: <?= $agent_stats['inactive'] ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?= $code_stats['summary']['total'] ?? 0 ?></div>
                    <div class="stat-label">Total Codes</div>
                    <div style="margin-top: 10px; font-size: 0.9rem; color: #7f8c8d;">
                        Active: <?= $code_stats['summary']['active'] ?? 0 ?> | 
                        Used: <?= $code_stats['summary']['used'] ?? 0 ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number">
                        <?= $code_stats['summary']['active'] ?? 0 ?>
                    </div>
                    <!-- Following your dashboard naming convention -->
                    <div class="stat-label">Un-used Codes</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?= $code_stats['summary']['used'] ?? 0 ?></div>
                    <div class="stat-label">Used Codes</div>
                </div>
            </div>

            <!-- Export Options -->
            <div class="content-section">
                <h3>📥 Export Data</h3>
                <p>Download filtered data in CSV format:</p>
                
                <div class="export-options">
                    <a href="?start=<?= $start ?>&end=<?= $end ?>&export=1&export_type=summary" 
                       class="export-btn summary">
                        📋 Summary Report
                    </a>
                    
                    <a href="?start=<?= $start ?>&end=<?= $end ?>&export=1&export_type=agents" 
                       class="export-btn agents">
                        👥 Agents Data
                    </a>
                    
                    <a href="?start=<?= $start ?>&end=<?= $end ?>&export=1&export_type=codes" 
                       class="export-btn codes">
                        🔑 Codes Data
                    </a>
                </div>
            </div>

            <!-- Detailed Statistics -->
            <div class="content-grid">
                <!-- Codes by Agent -->
                <?php if (!empty($code_stats['by_agent'])): ?>
                <div class="content-section">
                    <h3>Top Agents by Codes</h3>
                    <ul class="item-list">
                        <?php 
                        $total_codes = $code_stats['summary']['total'] ?? 1;
                        foreach ($code_stats['by_agent'] as $agent): 
                            $percentage = round(($agent['code_count'] / $total_codes) * 100, 1);
                        ?>
                        <li>
                            <div class="item-info">
                                <h4><?= htmlspecialchars($agent['name'] ?: 'Unassigned') ?></h4>
                                <p>Code Count: <?= $agent['code_count'] ?></p>
                                <div class="percentage-bar">
                                    <div class="percentage-fill" style="width: <?= min($percentage, 100) ?>%"></div>
                                </div>
                            </div>
                            <span style="font-weight: bold; color: #3498db;"><?= $percentage ?>%</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Recent Agents -->
                <div class="content-section">
                    <h3>Recent Agents (All Time)</h3>
                    <?php if (count($recent_agents) > 0): ?>
                        <ul class="item-list">
                            <?php foreach ($recent_agents as $agent): ?>
                                <li>
                                    <div class="item-info">
                                        <h4><?= htmlspecialchars($agent['name']) ?></h4>
                                        <p><?= htmlspecialchars($agent['email']) ?></p>
                                        <p>Registered: <?= date('M j, Y', strtotime($agent['created_at'])) ?></p>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="no-data">No agents registered.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Code Status Breakdown -->
            <?php if ($code_stats['summary']): ?>
            <div class="content-section">
                <h3>Code Status Breakdown</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; font-weight: bold; color: #2ecc71;"><?= $code_stats['summary']['active'] ?? 0 ?></div>
                        <div style="color: #666;">Active Codes</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; font-weight: bold; color: #3498db;"><?= $code_stats['summary']['used'] ?? 0 ?></div>
                        <div style="color: #666;">Used Codes</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; font-weight: bold; color: #e74c3c;"><?= $code_stats['summary']['expired'] ?? 0 ?></div>
                        <div style="color: #666;">Expired Codes</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- No filters selected -->
            <div class="content-section">
                <div class="no-data">
                    <h3>📊 Ready to Generate Reports</h3>
                    <p>Select a date range above to view statistics and export data.</p>
                    <p style="margin-top: 20px;">
                        <button onclick="setDefaultDates()" class="btn" style="margin-right: 10px;">
                            View Last 30 Days
                        </button>
                        <button onclick="setCurrentMonth()" class="btn">
                            View This Month
                        </button>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quick Stats Info -->
        <div class="content-section" style="margin-top: 30px;">
            <h3>Report Information</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div>
                    <h4 style="color: #3498db; margin-bottom: 10px;">📋 Summary Report</h4>
                    <p style="font-size: 0.9rem; color: #666;">High-level statistics including total agents and codes with breakdowns.</p>
                </div>
                <div>
                    <h4 style="color: #9b59b6; margin-bottom: 10px;">👥 Agents Data</h4>
                    <p style="font-size: 0.9rem; color: #666;">Detailed agent information including contact details and registration dates.</p>
                </div>
                <div>
                    <h4 style="color: #e74c3c; margin-bottom: 10px;">🔑 Codes Data</h4>
                    <p style="font-size: 0.9rem; color: #666;">Complete code information including status, assignment, and usage dates.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Date validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const start = document.querySelector('input[name="start"]').value;
        const end = document.querySelector('input[name="end"]').value;
        
        if (start && end && new Date(start) > new Date(end)) {
            alert('Start date cannot be after end date!');
            e.preventDefault();
        }
    });

    // Auto-set dates
    function setDefaultDates() {
        const endDate = new Date();
        const startDate = new Date();
        startDate.setDate(startDate.getDate() - 30);
        
        document.querySelector('input[name="start"]').value = startDate.toISOString().split('T')[0];
        document.querySelector('input[name="end"]').value = endDate.toISOString().split('T')[0];
        document.querySelector('form').submit();
    }

    function setCurrentMonth() {
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        
        document.querySelector('input[name="start"]').value = firstDay.toISOString().split('T')[0];
        document.querySelector('input[name="end"]').value = lastDay.toISOString().split('T')[0];
        document.querySelector('form').submit();
    }

    // Auto-set end date to today on page load
    window.addEventListener('load', function() {
        const endDateInput = document.querySelector('input[name="end"]');
        if (!endDateInput.value) {
            endDateInput.value = new Date().toISOString().split('T')[0];
        }
        
        // Auto-set start date to 30 days ago if empty
        const startDateInput = document.querySelector('input[name="start"]');
        if (!startDateInput.value && endDateInput.value) {
            const endDate = new Date(endDateInput.value);
            const startDate = new Date(endDate);
            startDate.setDate(startDate.getDate() - 30);
            startDateInput.value = startDate.toISOString().split('T')[0];
        }
    });
    </script>
</body>
</html>