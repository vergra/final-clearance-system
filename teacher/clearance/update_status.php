<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin', 'teacher']);

$pdo = getDB();
$user = getCurrentUser();
$teacher_id = $user['reference_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$clearance_id = (int)($_POST['clearance_id'] ?? 0);
$status = $_POST['status'] ?? '';
$remarks = trim($_POST['remarks'] ?? '');

if ($clearance_id <= 0 || !in_array($status, ['Approved', 'Declined'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

try {
    // Verify teacher owns this clearance record
    $stmt = $pdo->prepare("
        SELECT clearance_id, teacher_id, status 
        FROM clearance_status 
        WHERE clearance_id = ? AND (teacher_id = ? OR ? = 'admin')
    ");
    $stmt->execute([$clearance_id, $teacher_id, $user['role']]);
    $clearance = $stmt->fetch();

    if (!$clearance) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Clearance record not found or access denied']);
        exit;
    }

    // Update clearance status
    $updateStmt = $pdo->prepare("
        UPDATE clearance_status 
        SET status = ?, remarks = ?, date_cleared = ? 
        WHERE clearance_id = ?
    ");
    
    $date_cleared = ($status === 'Approved') ? date('Y-m-d') : null;
    $updateStmt->execute([$status, $remarks, $date_cleared, $clearance_id]);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Clearance status updated successfully']);

} catch (PDOException $e) {
    error_log("Database error in update_status: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
?>
