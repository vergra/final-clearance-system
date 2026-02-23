<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    try {
        $pdo->prepare("DELETE FROM departments WHERE department_id = ?")->execute([$id]);
    } catch (PDOException $e) { /* ignore FK */ }
}
header('Location: index.php?deleted=1');
exit;
