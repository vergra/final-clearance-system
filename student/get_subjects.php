<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');

$strand_id = (int)($_GET['strand_id'] ?? 0);
if (!$strand_id) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("SELECT subject_id, subject_name FROM subjects WHERE strand_id = ? ORDER BY subject_name");
$stmt->execute([$strand_id]);
$subjects = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($subjects);
?>
