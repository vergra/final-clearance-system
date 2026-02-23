<?php
// Test department functionality
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/student_clearance/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

require_once 'config/database.php';
require_once 'includes/auth.php';

echo "<h2>Department Functions Test</h2>";

// Test if admin is logged in
$currentUser = getCurrentUser();
if (!$currentUser || $currentUser['role'] !== 'admin') {
    echo "<p style='color: red;'>✗ You must be logged in as admin to test this</p>";
    echo "<p><a href='public/login.php?as=admin'>Login as admin</a></p>";
    exit;
}

echo "<p style='color: green;'>✓ Logged in as admin</p>";

// Test database connection and query
try {
    $pdo = getDB();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test department query
    $stmt = $pdo->query("SELECT * FROM departments ORDER BY department_name");
    $departments = $stmt->fetchAll();
    
    echo "<h3>Current Departments:</h3>";
    if (empty($departments)) {
        echo "<p>No departments found</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 300px;'>";
        echo "<tr><th>ID</th><th>Name</th></tr>";
        foreach ($departments as $dept) {
            echo "<tr><td>" . $dept['department_id'] . "</td><td>" . htmlspecialchars($dept['department_name']) . "</td></tr>";
        }
        echo "</table>";
    }
    
    // Test add department
    echo "<h3>Test Add Department:</h3>";
    if (isset($_GET['add_test'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO departments (department_name) VALUES (?)");
            $stmt->execute(['Test Department ' . date('H:i:s')]);
            echo "<p style='color: green;'>✓ Test department added successfully</p>";
            echo "<p><a href='test_departments.php'>Refresh to see updated list</a></p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Error adding department: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p><a href='test_departments.php?add_test=1'>Click to add test department</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}

echo "<h3>Links to actual department pages:</h3>";
echo "<p><a href='admin/departments/index.php'>Go to Departments Index</a></p>";
echo "<p><a href='admin/departments/create.php'>Go to Add Department</a></p>";
?>
