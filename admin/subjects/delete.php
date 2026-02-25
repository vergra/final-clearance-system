<?php
$id_qs = (int)($_GET['id'] ?? 0);
$strand_qs = (int)($_GET['strand_id'] ?? 0);
if ($strand_qs) {
    header('Location: ../departments/delete_subject.php?id=' . $id_qs . '&strand_id=' . $strand_qs);
} else {
    header('Location: ../departments/index.php');
}
exit;

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    try {
        $pdo->prepare("DELETE FROM subjects WHERE subject_id = ?")->execute([$id]);
    } catch (PDOException $e) { }
}
header('Location: index.php?deleted=1');
exit;
