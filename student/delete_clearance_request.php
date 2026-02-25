<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');
$pdo = getDB();
$user = getCurrentUser();
$lrn = $user['reference_id'] ?? null;

$request_group_id = $_GET['request_group_id'] ?? '';
$school_year_id = (int)($_GET['school_year_id'] ?? 0);
$date_submitted = $_GET['date_submitted'] ?? '';

if (!$lrn) {
    header('Location: my_clearance.php');
    exit;
}

try {
    if ($request_group_id !== '') {
        $stmt = $pdo->prepare('DELETE FROM clearance_status WHERE lrn = ? AND request_group_id = ?');
        $stmt->execute([$lrn, $request_group_id]);
    } elseif ($school_year_id && $date_submitted !== '') {
        $stmt = $pdo->prepare('DELETE FROM clearance_status WHERE lrn = ? AND school_year_id = ? AND date_submitted = ?');
        $stmt->execute([$lrn, $school_year_id, $date_submitted]);
    }
} catch (PDOException $e) {
}

header('Location: my_clearance.php');
exit;
?>
