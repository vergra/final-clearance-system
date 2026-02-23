<?php
// Test database connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

// Test basic connection
try {
    require_once 'config/database.php';
    echo "<p style='color: green;'>✓ Database config loaded successfully</p>";
    
    $pdo = getDB();
    echo "<p style='color: green;'>✓ Database connection established</p>";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✓ Query executed successfully - Users count: " . $result['count'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Session Test</h2>";

// Test session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "<p style='color: green;'>✓ Session started</p>";

// Test auth
try {
    require_once 'includes/auth.php';
    echo "<p style='color: green;'>✓ Auth file loaded successfully</p>";
    
    $user = getCurrentUser();
    if ($user === null) {
        echo "<p style='color: orange;'>⚠ No user currently logged in</p>";
    } else {
        echo "<p style='color: green;'>✓ Current user: " . htmlspecialchars($user['username']) . " (Role: " . $user['role'] . ")</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Auth error: " . $e->getMessage() . "</p>";
}

echo "<h2>PHP Info</h2>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>MySQL extension loaded: " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "</p>";
echo "<p>Session support: " . (session_status() !== PHP_SESSION_DISABLED ? 'Yes' : 'No') . "</p>";
?>
