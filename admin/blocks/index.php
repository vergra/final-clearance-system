<?php
$baseUrl = '..';
$pageTitle = 'Blocks';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$stmt = $pdo->query("SELECT * FROM blocks ORDER BY block_code");
$rows = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Blocks</h1>
        <p class="text-muted mb-0">Manage sections and class blocks</p>
    </div>
    <div>
        <a href="../../public/index.php" class="btn btn-outline-secondary me-2">← Back to Dashboard</a>
        <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Block</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (isset($_GET['created'])): ?>
            <div class="alert alert-success py-2">Block created.</div>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success py-2">Block updated.</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success py-2">Block deleted.</div>
        <?php endif; ?>
        <?php if (empty($rows)): ?>
            <p class="text-muted mb-0">No blocks yet. <a href="create.php">Add one</a>.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Block code</th>
                            <th>Block name</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['block_code']); ?></td>
                                <td><?php echo htmlspecialchars($r['block_name']); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?code=<?php echo urlencode($r['block_code']); ?>" class="btn btn-outline-primary">Edit</a>
                                        <a href="delete.php?code=<?php echo urlencode($r['block_code']); ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this block?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
