<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

session_start();

echo "<h1>Debug Student Information</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ No user_id in session - not logged in</p>";
    exit;
}

echo "<p>✅ User ID in session: " . $_SESSION['user_id'] . "</p>";

// Get current user
try {
    $user = getCurrentUser();
    if (!$user) {
        echo "<p style='color: red;'>❌ getCurrentUser() returned null</p>";
        exit;
    }
    
    echo "<h2>Current User Info:</h2>";
    echo "<pre>";
    print_r($user);
    echo "</pre>";
    
    if (!isset($user['reference_id']) || !$user['reference_id']) {
        echo "<p style='color: red;'>❌ No reference_id (LRN) found in user data</p>";
        exit;
    }
    
    $lrn = $user['reference_id'];
    echo "<p>✅ LRN found: " . $lrn . "</p>";
    
    // Check if this LRN exists in students table
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM students WHERE lrn = ?");
    $stmt->execute([$lrn]);
    $student = $stmt->fetch();
    
    if ($student) {
        echo "<h2>✅ Student Record Found:</h2>";
        echo "<pre>";
        print_r($student);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>❌ No student record found for LRN: " . $lrn . "</p>";
        
        // Show all students in database for reference
        echo "<h3>All Students in Database:</h3>";
        $allStudents = $pdo->query("SELECT lrn, surname, given_name FROM students LIMIT 10")->fetchAll();
        echo "<pre>";
        print_r($allStudents);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='../student/request_clearance.php'>← Back to Clearance Form</a></p>";
?>
