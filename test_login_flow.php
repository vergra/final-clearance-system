<?php
// Test the exact login flow
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

echo "<h2>Login Flow Test</h2>";
echo "<p>Session ID: " . session_id() . "</p>";

if (isset($_GET['do_login'])) {
    echo "<h3>Attempting login...</h3>";
    
    $username = 'admin';
    $password = 'admin123';
    $as = 'admin';
    
    $loginResult = attemptLogin($username, $password, $as);
    echo "<p>Login result: " . var_export($loginResult, true) . "</p>";
    
    if ($loginResult === true) {
        echo "<p style='color: green;'>✓ Login successful!</p>";
        echo "<p>Session after login:</p>";
        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
        
        $currentUser = getCurrentUser();
        echo "<p>Current user after login:</p>";
        echo "<pre>" . print_r($currentUser, true) . "</pre>";
        
        if ($currentUser && $currentUser['role'] === 'admin') {
            echo "<p style='color: green;'>✓ Should show admin dashboard!</p>";
            echo "<p><a href='public/index.php'>Go to admin dashboard</a></p>";
        } else {
            echo "<p style='color: red;'>✗ Current user is null or not admin</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Login failed</p>";
    }
} else {
    echo "<h3>Current state:</h3>";
    $currentUser = getCurrentUser();
    if ($currentUser) {
        echo "<p style='color: green;'>✓ Already logged in as: " . $currentUser['username'] . " (" . $currentUser['role'] . ")</p>";
        echo "<p><a href='public/index.php'>Go to admin dashboard</a></p>";
    } else {
        echo "<p style='color: orange;'>⚠ Not logged in</p>";
        echo "<p><a href='test_login_flow.php?do_login=1'>Click here to test admin login</a></p>";
    }
}
?>
