<?php
// Simple database connection test
require_once __DIR__ . '/config/database.php';

echo "<h2>Database Connection Test</h2>";

try {
    $pdo = getDB();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test basic queries
    echo "<h3>Testing Queries:</h3>";
    
    // Test departments
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM departments");
    $count = $stmt->fetch();
    echo "<p>Departments: " . $count['count'] . " records</p>";
    
    // Test strands
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM strands");
    $count = $stmt->fetch();
    echo "<p>Strands: " . $count['count'] . " records</p>";
    
    // Test subjects
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM subjects");
    $count = $stmt->fetch();
    echo "<p>Subjects: " . $count['count'] . " records</p>";
    
    // Show actual data
    echo "<h3>Departments:</h3>";
    $stmt = $pdo->query("SELECT department_id, department_name FROM departments ORDER BY department_name");
    while ($row = $stmt->fetch()) {
        echo "<p>ID: " . $row['department_id'] . " - " . htmlspecialchars($row['department_name']) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
