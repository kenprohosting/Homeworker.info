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

function extractSchema(string $sql): array {
    $schema = [];
    preg_match_all('/CREATE TABLE\s+`?(\w+)`?\s*\((.*?)\);/si', $sql, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $table = $match[1];
        $colsRaw = $match[2];
        $cols = [];
        $lines = preg_split('/,\r?\n/', trim($colsRaw));
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^(PRIMARY|UNIQUE|KEY|CONSTRAINT|INDEX)/i', $line)) continue;
            if (preg_match('/^`(\w+)`\s+(.+)/', $line, $colMatch)) {
                $colName = $colMatch[1];
                $colDef = $colMatch[2];
                $cols[$colName] = $colDef;
            }
        }
        $schema[$table] = $cols;
    }
    return $schema;
}

function getDBColumns(PDO $pdo, string $table, string $dbName): array {
    $stmt = $pdo->prepare("SELECT COLUMN_NAME, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
    $stmt->execute([$dbName, $table]);
    $cols = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cols[$row['COLUMN_NAME']] = $row['COLUMN_TYPE'];
    }
    return $cols;
}

function tableExists(PDO $pdo, string $tableName): bool {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$tableName]);
    return $stmt->fetch() !== false;
}

// Handle running a fix command
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_sql']) && !empty($_POST['run_sql'])) {
    $sqlToRun = $_POST['run_sql'];
    try {
        $pdo->exec($sqlToRun);
        echo "<div style='color:green;font-weight:bold;'>✅ Successfully ran:<br><code>" . htmlspecialchars($sqlToRun) . "</code></div>";
    } catch (PDOException $e) {
        echo "<div style='color:red;font-weight:bold;'>❌ Error running:<br><code>" . htmlspecialchars($sqlToRun) . "</code><br>Error: " . $e->getMessage() . "</div>";
    }
}

// If uploaded schema file present, show diff and fixes
if (isset($_FILES['sqlfile']) && $_FILES['sqlfile']['error'] === UPLOAD_ERR_OK) {
    $sqlContent = file_get_contents($_FILES['sqlfile']['tmp_name']);
    $uploadedSchema = extractSchema($sqlContent);

    echo "<h2>Schema Fix Commands</h2>";

    echo '<form method="post">';
    foreach ($uploadedSchema as $table => $columns) {
        echo "<h3>Table: $table</h3>";

        if (!tableExists($pdo, $table)) {
            echo "<div style='color:red;'>Table does NOT exist in DB.</div>";
            // Extract full CREATE TABLE for this table only (simplified)
            preg_match('/CREATE TABLE\s+`?' . preg_quote($table, '/') . '`?\s*\((.*?)\);/si', $sqlContent, $tableMatch);
            if ($tableMatch) {
                $createStmt = "CREATE TABLE `$table` (" . trim($tableMatch[1]) . ");";
                echo "<pre style='background:#222;color:#0f0;padding:10px;border-radius:5px;'>$createStmt</pre>";
                echo '<button type="submit" name="run_sql" value="' . htmlspecialchars($createStmt, ENT_QUOTES) . '">Run CREATE TABLE</button>';
            }
            continue;
        }

        $dbColumns = getDBColumns($pdo, $table, $dbname);

        $missingCols = array_diff_key($columns, $dbColumns);

        $typeMismatches = [];
        foreach ($columns as $col => $def) {
            if (isset($dbColumns[$col])) {
                $uploadedType = strtolower(preg_replace('/\s+.*/', '', $def));
                $dbType = strtolower($dbColumns[$col]);
                if ($uploadedType !== $dbType) {
                    $typeMismatches[$col] = ['uploaded' => $def, 'db' => $dbColumns[$col]];
                }
            }
        }

        if (empty($missingCols) && empty($typeMismatches)) {
            echo "<div style='color:green;'>✅ Table structure matches perfectly!</div>";
        } else {
            if ($missingCols) {
                echo "<div><strong>Missing columns — add these:</strong></div>";
                foreach ($missingCols as $col => $def) {
                    $sqlCmd = "ALTER TABLE `$table` ADD COLUMN `$col` $def;";
                    echo "<pre style='background:#222;color:#0f0;padding:10px;border-radius:5px;'>$sqlCmd</pre>";
                    echo '<button type="submit" name="run_sql" value="' . htmlspecialchars($sqlCmd, ENT_QUOTES) . '">Run ADD COLUMN</button>';
                }
            }
            if ($typeMismatches) {
                echo "<div><strong>Columns with type mismatch — modify these:</strong></div>";
                foreach ($typeMismatches as $col => $types) {
                    $sqlCmd = "ALTER TABLE `$table` MODIFY COLUMN `$col` {$types['uploaded']};";
                    echo "<pre style='background:#222;color:#f90;padding:10px;border-radius:5px;'>$sqlCmd</pre>";
                    echo '<button type="submit" name="run_sql" value="' . htmlspecialchars($sqlCmd, ENT_QUOTES) . '">Run MODIFY COLUMN</button>';
                }
            }
        }
    }
    echo '</form>';
} else {
    // Show upload form if no file uploaded yet
    ?>
    <form method="post" enctype="multipart/form-data">
        <label>Upload your SQL schema file:</label><br>
        <input type="file" name="sqlfile" accept=".sql" required><br><br>
        <button type="submit">Show Fix Commands</button>
    </form>
    <?php
}
?>