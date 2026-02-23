<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT signup_request_id, lrn, requested_username, password_hash, status FROM signup_requests WHERE signup_request_id = ?');
$stmt->execute([$id]);
$req = $stmt->fetch();
if (!$req || $req['status'] !== 'pending') {
    header('Location: index.php');
    exit;
}

$adminId = getCurrentUser()['user_id'];
try {
    $pdo->beginTransaction();
    $ins = $pdo->prepare('INSERT INTO users (username, password_hash, role, reference_id) VALUES (?, ?, ?, ?)');
    $ins->execute([$req['requested_username'], $req['password_hash'], 'student', $req['lrn']]);
    $pdo->prepare('UPDATE signup_requests SET status = ?, reviewed_at = NOW(), reviewed_by = ? WHERE signup_request_id = ?')->execute(['approved', $adminId, $id]);
    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    if ($e->getCode() == 23000) {
        $_SESSION['signup_error'] = 'Username already exists. Decline this request or choose a different action.';
    }
    header('Location: index.php?error=1');
    exit;
}
header('Location: index.php?approved=1');
exit;
