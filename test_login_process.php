<?php
// Test login process step by step
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Login Process Test</h2>";

session_start();

// Step 1: Test hardcoded admin login
echo "<h3>Step 1: Testing hardcoded admin login</h3>";
$username = 'admin';
$password = 'admin123';

require_once 'config/database.php';
require_once 'includes/auth.php';

try {
    $loginResult = attemptLogin($username, $password, 'admin');
    echo "<p>Login result: " . var_export($loginResult, true) . "</p>";
    
    if ($loginResult === true) {
        echo "<p style='color: green;'>✓ Login successful</p>";
        echo "<p>Session data: " . print_r($_SESSION, true) . "</p>";
        
        $user = getCurrentUser();
        echo "<p>Current user: " . print_r($user, true) . "</p>";
        
        if ($user && $user['role'] === 'admin') {
            echo "<p style='color: green;'>✓ Admin user confirmed</p>";
        } else {
            echo "<p style='color: red;'>✗ Admin user not found or wrong role</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Login failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

// Step 2: Test redirect logic
echo "<h3>Step 2: Testing redirect logic</h3>";
$redirect = $_SESSION['login_redirect'] ?? rtrim(WEB_BASE, '/') . '/public/index.php';
echo "<p>Would redirect to: $redirect</p>";

// Step 3: Test what index.php would show
echo "<h3>Step 3: Testing what index.php would show</h3>";
$currentUser = getCurrentUser();
if ($currentUser === null) {
    echo "<p style='color: orange;'>⚠ Would show landing page (no user)</p>";
} else {
    echo "<p style='color: green;'>✓ Would show dashboard for: " . $currentUser['role'] . "</p>";
}

// Clear session for next test
logout();
echo "<p>Session cleared for next test</p>";
?>
