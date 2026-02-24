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
    SELECT DISTINCT t.teacher_id, t.surname, t.given_name 
    FROM teachers t 
    JOIN teacher_subject ts ON ts.teacher_id = t.teacher_id
    WHERE ts.subject_id = ? AND ts.school_year_id = (SELECT school_year_id FROM school_year ORDER BY year_label DESC LIMIT 1)
    ORDER BY t.surname, t.given_name
");
$stmt->execute([$subject_id]);
$teachers = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($teachers);
?>
