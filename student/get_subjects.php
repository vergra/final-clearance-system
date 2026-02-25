<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');

$strand_id = (int)($_GET['strand_id'] ?? 0);
$strand_name = $_GET['strand_name'] ?? '';

if (!$strand_id && !$strand_name) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$pdo = getDB();

if ($strand_id) {
    $stmt = $pdo->prepare("SELECT subject_id, subject_name FROM subjects WHERE strand_id = ? ORDER BY subject_name");
    $stmt->execute([$strand_id]);
} else {
    $stmt = $pdo->prepare("SELECT s.subject_id, s.subject_name FROM subjects s 
                           LEFT JOIN strands st ON st.strand_id = s.strand_id 
                           WHERE st.strand_name = ? ORDER BY s.subject_name");
    $stmt->execute([$strand_name]);
}

$subjects = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($subjects);
?>
