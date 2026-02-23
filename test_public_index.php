<?php
// Test the exact public/index.php file
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Public Index File</h2>";

// Simulate the exact conditions of public/index.php
$_SERVER['REQUEST_URI'] = '/student_clearance/public/index.php';

try {
    echo "<p>Starting public/index.php simulation...</p>";
    
    $pageTitle = 'Home';
    echo "<p>✓ Page title set</p>";
    
    require_once __DIR__ . '/../config/database.php';
    echo "<p>✓ Database config loaded from ../config/database.php</p>";
    
    require_once __DIR__ . '/../includes/auth.php';
    echo "<p>✓ Auth loaded from ../includes/auth.php</p>";
    
    $currentUser = getCurrentUser();
    echo "<p>✓ Current user: " . ($currentUser ? $currentUser['username'] : 'null') . "</p>";
    
    if ($currentUser === null) {
        echo "<p>✓ User not logged in - showing landing page logic</p>";
        $base = rtrim(WEB_BASE, '/');
        echo "<p>✓ Base URL: $base</p>";
        
        require_once __DIR__ . '/../includes/header.php';
        echo "<p>✓ Header loaded from ../includes/header.php</p>";
        
        // Test the landing page content
        echo "<div class='row mb-4'>
            <div class='col text-center'>
                <h1 class='h2'>Gradline: Senior High School Clearance</h1>
                <p class='text-muted'>Digitize and streamline the clearance process. Choose how you want to sign in.</p>
            </div>
        </div>";
        echo "<p>✓ Landing page content rendered</p>";
        
        require_once __DIR__ . '/../includes/footer.php';
        echo "<p>✓ Footer loaded from ../includes/footer.php</p>";
        
    } else {
        echo "<p>✓ User logged in as: " . $currentUser['role'] . "</p>";
        
        $pdo = getDB();
        echo "<p>✓ Database connection established</p>";
        
        require_once __DIR__ . '/../includes/header.php';
        echo "<p>✓ Header loaded for logged in user</p>";
        
        require_once __DIR__ . '/../includes/footer.php';
        echo "<p>✓ Footer loaded for logged in user</p>";
    }
    
    echo "<p style='color: green; font-weight: bold;'>✓ All tests passed! The public/index.php should work fine.</p>";
    
} catch (Error $e) {
    echo "<p style='color: red;'>✗ Fatal Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Exception: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
}
?>
