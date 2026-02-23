<?php
$baseUrl = '..';
$pageTitle = 'Subjects';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$stmt = $pdo->query("
    SELECT s.subject_id, s.subject_name, s.strand, s.department_id, d.department_name
    FROM subjects s
    LEFT JOIN departments d ON d.department_id = s.department_id
    ORDER BY s.subject_name
");
$rows = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Subjects</h1>
    <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Subject</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (isset($_GET['created'])): ?>
            <div class="alert alert-success py-2">Subject created.</div>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success py-2">Subject updated.</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success py-2">Subject deleted.</div>
        <?php endif; ?>
        <?php if (empty($rows)): ?>
            <p class="text-muted mb-0">No subjects yet. <a href="create.php">Add one</a>.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Subject name</th>
                            <th>Strand</th>
                            <th>Department</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?php echo (int)$r['subject_id']; ?></td>
                                <td><?php echo htmlspecialchars($r['subject_name']); ?></td>
                                <td><?php echo htmlspecialchars($r['strand']); ?></td>
                                <td><?php echo htmlspecialchars($r['department_name'] ?? '-'); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?php echo (int)$r['subject_id']; ?>" class="btn btn-outline-primary">Edit</a>
                                        <a href="delete.php?id=<?php echo (int)$r['subject_id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this subject?');">Delete</a>
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
