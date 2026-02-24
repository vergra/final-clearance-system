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
$stmt = $pdo->prepare("SELECT requirement_id, requirement_name FROM requirements WHERE department_id = ? ORDER BY requirement_name");
$stmt->execute([$department_id]);
$requirements = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($requirements);
?>
