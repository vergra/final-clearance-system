<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin', 'teacher']);
$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    try {
        $pdo->prepare("DELETE FROM clearance_status WHERE clearance_id = ?")->execute([$id]);
    } catch (PDOException $e) { }
}
header('Location: index.php?deleted=1');
exit;
