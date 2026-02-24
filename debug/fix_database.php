<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDB();
    
    echo "<h1>Fix Missing Database Columns</h1>";
    
    // Check and add department_id to strands table
    echo "<h2>Checking strands table...</h2>";
    $stmt = $pdo->prepare("SHOW COLUMNS FROM strands LIKE 'department_id'");
    $stmt->execute();
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        echo "<p>Adding department_id to strands table...</p>";
        $pdo->exec("ALTER TABLE strands ADD COLUMN department_id INT NOT NULL DEFAULT 1");
        echo "<p style='color: green;'>✅ department_id added to strands</p>";
    } else {
        echo "<p style='color: green;'>✅ department_id already exists in strands</p>";
    }
    
    // Check and add created_at to strands table
    $stmt = $pdo->prepare("SHOW COLUMNS FROM strands LIKE 'created_at'");
    $stmt->execute();
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        echo "<p>Adding created_at to strands table...</p>";
        $pdo->exec("ALTER TABLE strands ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "<p style='color: green;'>✅ created_at added to strands</p>";
    } else {
        echo "<p style='color: green;'>✅ created_at already exists in strands</p>";
    }
    
    // Add foreign key constraint if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE strands ADD CONSTRAINT fk_strands_department FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE");
        echo "<p style='color: green;'>✅ Foreign key constraint added to strands</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Foreign key constraint may already exist: " . $e->getMessage() . "</p>";
    }
    
    // Check blocks table
    echo "<h2>Checking blocks table...</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'blocks'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "<p>Creating blocks table...</p>";
        $pdo->exec("
            CREATE TABLE blocks (
                block_code VARCHAR(20) PRIMARY KEY,
                block_name VARCHAR(100) DEFAULT NULL,
                department_id INT NOT NULL,
                FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "<p style='color: green;'>✅ blocks table created</p>";
    } else {
        echo "<p style='color: green;'>✅ blocks table already exists</p>";
        
        // Check if blocks has department_id
        $stmt = $pdo->prepare("SHOW COLUMNS FROM blocks LIKE 'department_id'");
        $stmt->execute();
        $columnExists = $stmt->fetch();
        
        if (!$columnExists) {
            echo "<p>Adding department_id to blocks table...</p>";
            $pdo->exec("ALTER TABLE blocks ADD COLUMN department_id INT NOT NULL DEFAULT 1");
            echo "<p style='color: green;'>✅ department_id added to blocks</p>";
        }
    }
    
    echo "<h2>✅ Database structure fixed!</h2>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='create_student.php'>← Create Student Record</a></p>";
?>
