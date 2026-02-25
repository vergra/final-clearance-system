<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');

$pdo = getDB();
$user = getCurrentUser();

// Debug: Check if user is logged in and has reference_id
if (!$user) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$lrn = $user['reference_id'];
if (!$lrn) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Student LRN not found in user session']);
    exit;
}

$department_id = (int)($_POST['department_id'] ?? 0);
$strand_name = $_POST['strand_id'] ?? ''; // This is now strand_name from the form
$school_year_id = (int)($_POST['school_year_id'] ?? 0);
$subjects = $_POST['subjects'] ?? [];

if (!$department_id || !$strand_name || !$school_year_id || empty($subjects)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $pdo->beginTransaction();

    $request_group_id = bin2hex(random_bytes(16));
    
    // Create a general requirement for this department if it doesn't exist
    $stmt = $pdo->prepare("
        SELECT requirement_id FROM requirements 
        WHERE department_id = ? AND requirement_name LIKE ?
    ");
    $stmt->execute([$department_id, "%Clearance%"]);
    $requirement = $stmt->fetch();
    
    if (!$requirement) {
        // Create a general clearance requirement
        $stmt = $pdo->prepare("
            INSERT INTO requirements (requirement_name, department_id) 
            VALUES (?, ?)
        ");
        $stmt->execute(["Subject Clearance", $department_id]);
        $requirement_id = $pdo->lastInsertId();
    } else {
        $requirement_id = $requirement['requirement_id'];
    }
    
    $submittedCount = 0;
    $duplicateCount = 0;
    
    // Process each subject-teacher pair
    foreach ($subjects as $subject) {
        $subject_id = (int)($subject['subject_id'] ?? 0);
        $teacher_id = (int)($subject['teacher_id'] ?? 0);
        
        if (!$subject_id || !$teacher_id) {
            continue;
        }
        
        // Check if request already exists
        $stmt = $pdo->prepare("
            SELECT clearance_id FROM clearance_status 
            WHERE lrn = ? AND teacher_id = ? AND school_year_id = ? AND subject_id = ?
        ");
        $stmt->execute([$lrn, $teacher_id, $school_year_id, $subject_id]);
        
        if ($stmt->fetch()) {
            $duplicateCount++;
            continue;
        }
        
        // Insert clearance request
        $stmt = $pdo->prepare("
            INSERT INTO clearance_status (lrn, requirement_id, teacher_id, subject_id, school_year_id, request_group_id, status, date_submitted) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pending', CURDATE())
        ");
        $stmt->execute([$lrn, $requirement_id, $teacher_id, $subject_id, $school_year_id, $request_group_id]);
        $submittedCount++;
    }
    
    $pdo->commit();
    
    $message = "Successfully submitted $submittedCount clearance request(s)";
    if ($duplicateCount > 0) {
        $message .= ". $duplicateCount request(s) were already submitted and were skipped.";
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => $message]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
