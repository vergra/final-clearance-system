<?php
// Debug admin login and dashboard
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Admin Login Debug</h2>";

session_start();

// Step 1: Test admin login
echo "<h3>Step 1: Testing admin login</h3>";
require_once 'config/database.php';
require_once 'includes/auth.php';

$username = 'admin';
$password = 'admin123';

try {
    $loginResult = attemptLogin($username, $password, 'admin');
    echo "<p>Login result: " . var_export($loginResult, true) . "</p>";
    
    if ($loginResult === true) {
        echo "<p style='color: green;'>✓ Login successful</p>";
        echo "<p>Session data after login:</p>";
        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
        
        $currentUser = getCurrentUser();
        echo "<p>Current user data:</p>";
        echo "<pre>" . print_r($currentUser, true) . "</pre>";
        
        // Step 2: Test what public/index.php would show
        echo "<h3>Step 2: Testing what public/index.php would show</h3>";
        if ($currentUser === null) {
            echo "<p style='color: red;'>✗ getCurrentUser() returned null - this is the problem!</p>";
        } else {
            echo "<p style='color: green;'>✓ User found: " . $currentUser['username'] . " (Role: " . $currentUser['role'] . ")</p>";
            
            if ($currentUser['role'] === 'admin') {
                echo "<p style='color: green;'>✓ Should show admin dashboard</p>";
                
                // Test database queries for admin dashboard
                $pdo = getDB();
                $tables = ['school_year' => 'School Years', 'departments' => 'Departments', 'blocks' => 'Blocks', 'teachers' => 'Teachers', 'subjects' => 'Subjects', 'students' => 'Students'];
                foreach (array_keys($tables) as $t) {
                    try {
                        $stmt = $pdo->query("SELECT COUNT(*) FROM $t");
                        $count = (int) $stmt->fetchColumn();
                        echo "<p>✓ Table $t: $count records</p>";
                    } catch (PDOException $e) {
                        echo "<p style='color: red;'>✗ Error counting $t: " . $e->getMessage() . "</p>";
                    }
                }
            }
        }
    } else {
        echo "<p style='color: red;'>✗ Login failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

// Step 3: Test session persistence
echo "<h3>Step 3: Testing session persistence</h3>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session status: " . session_status() . "</p>";
echo "<p>Session save path: " . session_save_path() . "</p>";
?>
