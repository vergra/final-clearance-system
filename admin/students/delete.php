<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();
$lrn = trim($_GET['lrn'] ?? '');
if ($lrn !== '') {
    try {
        $pdo->prepare("DELETE FROM students WHERE lrn = ?")->execute([$lrn]);
    } catch (PDOException $e) { }
}
header('Location: index.php?deleted=1');
exit;
