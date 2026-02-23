<?php
$baseUrl = '..';
$pageTitle = 'Decline sign-up';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT signup_request_id, lrn, requested_username, status FROM signup_requests WHERE signup_request_id = ?');
$stmt->execute([$id]);
$req = $stmt->fetch();
if (!$req || $req['status'] !== 'pending') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $remarks = trim($_POST['remarks'] ?? '');
    $adminId = getCurrentUser()['user_id'];
    $pdo->prepare('UPDATE signup_requests SET status = ?, reviewed_at = NOW(), reviewed_by = ?, remarks = ? WHERE signup_request_id = ?')->execute(['declined', $adminId, $remarks ?: null, $id]);
    header('Location: index.php?declined=1');
    exit;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Decline sign-up request</h1>
    <a href="index.php" class="btn btn-outline-secondary">Back to list</a>
</div>

<div class="card">
    <div class="card-body">
        <p class="mb-2"><strong>LRN:</strong> <?php echo htmlspecialchars($req['lrn']); ?></p>
        <p class="mb-2"><strong>Requested username:</strong> <?php echo htmlspecialchars($req['requested_username']); ?></p>
        <form method="post" action="">
            <div class="mb-3">
                <label for="remarks" class="form-label">Remarks (optional)</label>
                <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="Reason for declining (e.g. incorrect details)"></textarea>
            </div>
            <button type="submit" class="btn btn-danger">Decline request</button>
            <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
