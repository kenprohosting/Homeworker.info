<?php
// setup.php

$host = 'localhost';
$dbname = 'esoma_homeworker';
$user = 'esoma_homeworker';
$pass = 'Kenyan@254';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// Extract tables and columns from uploaded SQL
function extractSchema(string $sql): array {
    $schema = [];
    // This regex grabs CREATE TABLE blocks
    preg_match_all('/CREATE TABLE\s+`?(\w+)`?\s*\((.*?)\);/si', $sql, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $table = $match[1];
        $colsRaw = $match[2];
        $cols = [];

        // Parse columns line by line
        $lines = preg_split('/,\r?\n/', trim($colsRaw));
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip keys, constraints etc.
            if (preg_match('/^(PRIMARY|UNIQUE|KEY|CONSTRAINT|INDEX)/i', $line)) continue;

            // Grab column name and type — simplified parsing
            if (preg_match('/^`(\w+)`\s+([^\s,]+)/', $line, $colMatch)) {
                $colName = $colMatch[1];
                $colType = $colMatch[2];
                $cols[$colName] = $colType;
            }
        }
        $schema[$table] = $cols;
    }
    return $schema;
}

// Get columns of a table from live DB
function getDBColumns(PDO $pdo, string $table, string $dbName): array {
    $stmt = $pdo->prepare("SELECT COLUMN_NAME, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
    $stmt->execute([$dbName, $table]);
    $cols = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cols[$row['COLUMN_NAME']] = $row['COLUMN_TYPE'];
    }
    return $cols;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sqlfile'])) {
    $sqlContent = file_get_contents($_FILES['sqlfile']['tmp_name']);
    $uploadedSchema = extractSchema($sqlContent);

    echo "<h2>Schema Diff Report</h2>";

    foreach ($uploadedSchema as $table => $columns) {
        echo "<h3>Table: $table</h3>";
        if (!tableExists($pdo, $table)) {
            echo "<div style='color:red;'>Table does NOT exist in DB.</div>";
            continue;
        }
        $dbColumns = getDBColumns($pdo, $table, $dbname);

        // Check missing columns in DB
        $missingCols = array_diff_key($columns, $dbColumns);
        // Check extra columns in DB (not in uploaded schema)
        $extraCols = array_diff_key($dbColumns, $columns);

        // Check columns with type differences
        $typeMismatches = [];
        foreach ($columns as $col => $type) {
            if (isset($dbColumns[$col]) && strtolower($dbColumns[$col]) !== strtolower($type)) {
                $typeMismatches[$col] = ['uploaded' => $type, 'db' => $dbColumns[$col]];
            }
        }

        if (empty($missingCols) && empty($extraCols) && empty($typeMismatches)) {
            echo "<div style='color:green;'>✅ Table structure matches perfectly!</div>";
        } else {
            if ($missingCols) {
                echo "<div style='color:orange;'>Missing columns in DB: " . implode(', ', array_keys($missingCols)) . "</div>";
            }
            if ($extraCols) {
                echo "<div style='color:blue;'>Extra columns in DB (not in uploaded schema): " . implode(', ', array_keys($extraCols)) . "</div>";
            }
            if ($typeMismatches) {
                echo "<div style='color:red;'>Columns with type mismatch:<ul>";
                foreach ($typeMismatches as $col => $types) {
                    echo "<li>$col: uploaded = {$types['uploaded']}, DB = {$types['db']}</li>";
                }
                echo "</ul></div>";
            }
        }
    }
}

// Helper: check table existence (reuse)
function tableExists(PDO $pdo, string $tableName): bool {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$tableName]);
    return $stmt->fetch() !== false;
}
?>

<!-- Simple upload form -->
<form method="post" enctype="multipart/form-data">
    <label>Upload your SQL schema file:</label><br>
    <input type="file" name="sqlfile" accept=".sql" required>
    <br><br>
    <button type="submit">Check Schema Diff</button>
</form>