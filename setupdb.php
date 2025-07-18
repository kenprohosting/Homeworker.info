<?php
// setup.php

$host = 'localhost';
$dbname = 'esoma_homeworker';
$user = 'esoma_homeworker';
$pass = 'Kenyan@254';

// Connect to DB
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass, [
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true // Needed for multiple queries
    ]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<h2>💀 DB Connection Failed:</h2><pre>" . $e->getMessage() . "</pre>");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sqlfile'])) {
    $sqlFile = $_FILES['sqlfile']['tmp_name'];

    if ($_FILES['sqlfile']['type'] !== 'application/sql' && pathinfo($_FILES['sqlfile']['name'], PATHINFO_EXTENSION) !== 'sql') {
        $message = "<div style='color:red;'>❌ Invalid file type. Please upload a .sql file.</div>";
    } else {
        $sqlContent = file_get_contents($sqlFile);

        try {
            $pdo->exec($sqlContent);
            $message = "<div style='color:green;'>✅ Schema uploaded & executed successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div style='color:red;'>❌ Execution failed:<br><pre>" . $e->getMessage() . "</pre></div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>📦 Upload DB Schema - Homeworker</title>
    <style>
        body { font-family: monospace; background: #111; color: #0f0; padding: 2em; }
        input[type="file"] { color: #0f0; }
        button { background: #0f0; color: #000; font-weight: bold; border: none; padding: 0.5em 1em; cursor: pointer; }
        .msg { margin-top: 1em; }
    </style>
</head>
<body>
    <h1>📤 Upload SQL Schema</h1>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="sqlfile" accept=".sql" required><br><br>
        <button type="submit">⚡ Upload & Execute</button>
    </form>
    <div class="msg"><?php echo $message; ?></div>
</body>
</html>
