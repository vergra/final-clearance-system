<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();
$code = trim($_GET['code'] ?? '');
if ($code !== '') {
    try {
        $pdo->prepare("DELETE FROM blocks WHERE block_code = ?")->execute([$code]);
    } catch (PDOException $e) { /* ignore FK */ }
}
header('Location: index.php?deleted=1');
exit;
