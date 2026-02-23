<?php
$baseUrl = '..';
$pageTitle = 'Students';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$stmt = $pdo->query("
    SELECT s.lrn, s.surname, s.middle_name, s.given_name, s.strand, s.block_code, b.block_name
    FROM students s
    LEFT JOIN blocks b ON b.block_code = s.block_code
    ORDER BY s.surname, s.given_name
");
$rows = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Students</h1>
        <p class="text-muted mb-0">Manage student records and clearance</p>
    </div>
    <div>
        <a href="../../public/index.php" class="btn btn-outline-secondary me-2">← Back to Dashboard</a>
        <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Student</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (isset($_GET['created'])): ?>
            <div class="alert alert-success py-2">Student created.</div>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success py-2">Student updated.</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success py-2">Student deleted.</div>
        <?php endif; ?>
        <?php if (empty($rows)): ?>
            <p class="text-muted mb-0">No students yet. <a href="create.php">Add one</a>.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>LRN</th>
                            <th>Name</th>
                            <th>Strand</th>
                            <th>Block</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['lrn']); ?></td>
                                <td><?php echo htmlspecialchars($r['surname'] . ', ' . $r['given_name'] . ($r['middle_name'] ? ' ' . $r['middle_name'] : '')); ?></td>
                                <td><?php echo htmlspecialchars($r['strand']); ?></td>
                                <td><?php echo htmlspecialchars($r['block_name'] ?? $r['block_code']); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?lrn=<?php echo urlencode($r['lrn']); ?>" class="btn btn-outline-primary">Edit</a>
                                        <a href="delete.php?lrn=<?php echo urlencode($r['lrn']); ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this student?');">Delete</a>
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
