<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');

$department_id = (int)($_GET['department_id'] ?? 0);
if (!$department_id) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("SELECT strand_id, strand_name FROM strands WHERE department_id = ? ORDER BY strand_name");
$stmt->execute([$department_id]);
$strands = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($strands);
?>
