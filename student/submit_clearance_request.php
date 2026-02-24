<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');

$pdo = getDB();
$user = getCurrentUser();
$lrn = $user['reference_id'];

$department_id = (int)($_POST['department_id'] ?? 0);
$strand_id = (int)($_POST['strand_id'] ?? 0);
$subject_id = (int)($_POST['subject_id'] ?? 0);
$teacher_id = (int)($_POST['teacher_id'] ?? 0);
$school_year_id = (int)($_POST['school_year_id'] ?? 0);

if (!$department_id || !$strand_id || !$subject_id || !$teacher_id || !$school_year_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Check if request already exists
    $stmt = $pdo->prepare("
        SELECT clearance_id FROM clearance_status 
        WHERE lrn = ? AND teacher_id = ? AND school_year_id = ? AND subject_id = ?
    ");
    $stmt->execute([$lrn, $teacher_id, $school_year_id, $subject_id]);
    
    if ($stmt->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Clearance request already exists for this teacher and subject']);
        exit;
    }
    
    // Create a general requirement for this subject if it doesn't exist
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
    
    // Insert clearance request
    $stmt = $pdo->prepare("
        INSERT INTO clearance_status (lrn, requirement_id, teacher_id, subject_id, school_year_id, status, date_submitted) 
        VALUES (?, ?, ?, ?, ?, 'Pending', CURDATE())
    ");
    $stmt->execute([$lrn, $requirement_id, $teacher_id, $subject_id, $school_year_id]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Clearance request submitted successfully']);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
