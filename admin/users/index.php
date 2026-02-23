<?php
$baseUrl = '..';
$pageTitle = 'User Accounts';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$stmt = $pdo->query("
    SELECT u.user_id, u.username, u.role, u.reference_id, u.created_at
    FROM users u
    ORDER BY u.role, u.username
");
$rows = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">User Accounts</h1>
    <a href="create.php" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Add User</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (isset($_GET['created'])): ?>
            <div class="alert alert-success py-2">User account created.</div>
        <?php endif; ?>
        <?php if (empty($rows)): ?>
            <p class="text-muted mb-0">No user accounts yet. <a href="create.php">Add one</a>.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Linked to (LRN / Teacher)</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['username']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($r['role']); ?></span></td>
                                <td><?php echo $r['reference_id'] ? htmlspecialchars($r['reference_id']) : '-'; ?></td>
                                <td><?php echo htmlspecialchars($r['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
