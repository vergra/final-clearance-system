<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin', 'teacher']);

$pdo = getDB();
$user = getCurrentUser();
$teacher_id = $user['reference_id'];

$clearance_id = (int)($_GET['id'] ?? 0);
if ($clearance_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid clearance ID']);
    exit;
}

try {
    // Get detailed clearance information with student data
    $stmt = $pdo->prepare("
        SELECT c.clearance_id, c.lrn, c.requirement_id, c.teacher_id, c.subject_id, c.school_year_id,
               c.status, c.date_submitted, c.date_cleared, c.remarks,
               s.surname AS st_surname, s.given_name AS st_given, s.middle_name AS st_middle_name,
               s.strand AS student_strand, s.block_code,
               r.requirement_name, sub.subject_name, sy.year_label,
               t.surname AS t_surname, t.given_name AS t_given
        FROM clearance_status c
        JOIN students s ON s.lrn = c.lrn
        JOIN requirements r ON r.requirement_id = c.requirement_id
        JOIN subjects sub ON sub.subject_id = c.subject_id
        JOIN school_year sy ON sy.school_year_id = c.school_year_id
        JOIN teachers t ON t.teacher_id = c.teacher_id
        WHERE c.clearance_id = ? AND (c.teacher_id = ? OR ? = 'admin')
    ");
    $stmt->execute([$clearance_id, $teacher_id, $user['role']]);
    $data = $stmt->fetch();

    if (!$data) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Clearance record not found or access denied']);
        exit;
    }

    // Format student name
    $student_name = $data['st_surname'] . ', ' . $data['st_given'];
    if (!empty($data['st_middle_name'])) {
        $student_name .= ' ' . $data['st_middle_name'];
    }

    // Prepare response
    $response = [
        'clearance_id' => $data['clearance_id'],
        'lrn' => $data['lrn'],
        'student_name' => $student_name,
        'block_code' => $data['block_code'],
        'strand' => $data['student_strand'],
        'requirement_name' => $data['requirement_name'],
        'subject_name' => $data['subject_name'],
        'year_label' => $data['year_label'],
        'status' => $data['status'],
        'date_submitted' => $data['date_submitted'],
        'date_cleared' => $data['date_cleared'],
        'remarks' => $data['remarks'],
        'teacher_name' => $data['t_surname'] . ', ' . $data['t_given']
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Database error in get_student_details: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error occurred']);
}
?>
