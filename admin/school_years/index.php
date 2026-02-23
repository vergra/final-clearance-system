<?php
$baseUrl = '..';
$pageTitle = 'School Years';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$stmt = $pdo->query("SELECT * FROM school_year ORDER BY year_label DESC");
$rows = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">School Years</h1>
        <p class="text-muted mb-0">Manage academic years and semesters</p>
    </div>
    <div>
        <a href="../../public/index.php" class="btn btn-outline-secondary me-2">← Back to Dashboard</a>
        <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add School Year</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success py-2">School year deleted.</div>
        <?php endif; ?>
        <?php if (empty($rows)): ?>
            <p class="text-muted mb-0">No school years yet. <a href="create.php">Add one</a>.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Year Label</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?php echo (int)$r['school_year_id']; ?></td>
                                <td><?php echo htmlspecialchars($r['year_label']); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?php echo (int)$r['school_year_id']; ?>" class="btn btn-outline-primary">Edit</a>
                                        <a href="delete.php?id=<?php echo (int)$r['school_year_id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this school year?');">Delete</a>
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
