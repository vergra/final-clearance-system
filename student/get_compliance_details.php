<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(['admin', 'student']);

$pdo = getDB();
$user = getCurrentUser();
$student_lrn = $user['reference_id'];

$clearance_id = (int)($_GET['id'] ?? 0);
if ($clearance_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid clearance ID']);
    exit;
}

try {
    // Add date_returned column if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE clearance_status ADD COLUMN date_returned DATE NULL AFTER date_cleared");
    } catch (PDOException $e) {
        // Column already exists, ignore error
    }
    
    // Get compliance details for the student's clearance
    $stmt = $pdo->prepare("
        SELECT c.clearance_id, c.lrn, c.requirement_id, c.teacher_id, c.subject_id, c.school_year_id,
               c.status, c.date_submitted, c.date_cleared, c.date_returned, c.remarks,
               s.surname AS st_surname, s.given_name AS st_given,
               r.requirement_name, sub.subject_name, sy.year_label,
               t.surname AS t_surname, t.given_name AS t_given
        FROM clearance_status c
        JOIN students s ON s.lrn = c.lrn
        JOIN requirements r ON r.requirement_id = c.requirement_id
        JOIN subjects sub ON sub.subject_id = c.subject_id
        JOIN school_year sy ON sy.school_year_id = c.school_year_id
        JOIN teachers t ON t.teacher_id = c.teacher_id
        WHERE c.clearance_id = ? AND c.lrn = ? AND c.status = 'Declined'
    ");
    $stmt->execute([$clearance_id, $student_lrn]);
    $data = $stmt->fetch();

    if (!$data) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Compliance record not found or access denied']);
        exit;
    }

    // Prepare response
    $response = [
        'clearance_id' => $data['clearance_id'],
        'lrn' => $data['lrn'],
        'requirements' => $data['remarks'],
        'teacher_name' => $data['t_surname'] . ', ' . $data['t_given'],
        'subject_name' => $data['subject_name'],
        'requirement_name' => $data['requirement_name'],
        'year_label' => $data['year_label'],
        'date_cleared' => $data['date_cleared'],
        'date_returned' => $data['date_returned'],
        'status' => $data['status']
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Database error in get_compliance_details: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
