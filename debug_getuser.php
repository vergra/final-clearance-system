<?php
// Debug getCurrentUser function
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

echo "<h2>Debug getCurrentUser Function</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<h3>Session data:</h3>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h3>Step-by-step getCurrentUser() logic:</h3>";

// Step 1: Check if user_id is empty
if (empty($_SESSION['user_id'])) {
    echo "<p style='color: red;'>✗ Step 1 FAILED: \$_SESSION['user_id'] is empty</p>";
} else {
    echo "<p style='color: green;'>✓ Step 1 PASSED: \$_SESSION['user_id'] = " . $_SESSION['user_id'] . "</p>";
    
    // Step 2: Check if it's hardcoded admin (user_id = 0)
    if ($_SESSION['user_id'] == 0 && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        echo "<p style='color: green;'>✓ Step 2 PASSED: Hardcoded admin detected</p>";
        $adminUser = [
            'user_id' => 0,
            'username' => $_SESSION['username'] ?? 'admin',
            'role' => 'admin',
            'reference_id' => null,
            'created_at' => null
        ];
        echo "<p>Would return admin user:</p>";
        echo "<pre>" . print_r($adminUser, true) . "</pre>";
    } else {
        echo "<p style='color: orange;'>⚠ Step 2: Not hardcoded admin, checking database...</p>";
        
        // Step 3: Check database
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare('SELECT user_id, username, role, reference_id, created_at FROM users WHERE user_id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if ($user) {
                echo "<p style='color: green;'>✓ Step 3 PASSED: Found user in database</p>";
                echo "<pre>" . print_r($user, true) . "</pre>";
            } else {
                echo "<p style='color: red;'>✗ Step 3 FAILED: No user found in database with user_id = " . $_SESSION['user_id'] . "</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Step 3 ERROR: " . $e->getMessage() . "</p>";
        }
    }
}

echo "<h3>Actual getCurrentUser() result:</h3>";
$actualResult = getCurrentUser();
if ($actualResult) {
    echo "<p style='color: green;'>✓ getCurrentUser() returned:</p>";
    echo "<pre>" . print_r($actualResult, true) . "</pre>";
} else {
    echo "<p style='color: red;'>✗ getCurrentUser() returned null</p>";
}
?>
