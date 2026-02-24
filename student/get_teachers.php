<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');

$subject_id = (int)($_GET['subject_id'] ?? 0);
if (!$subject_id) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT t.teacher_id, t.surname, t.given_name 
    FROM teachers t 
    WHERE t.subject_id = ? OR t.department_id = (SELECT department_id FROM subjects WHERE subject_id = ?)
    ORDER BY t.surname, t.given_name
");
$stmt->execute([$subject_id, $subject_id]);
$teachers = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($teachers);
?>
