<?php
$baseUrl = '..';
$pageTitle = 'Teachers';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

// Try to get teachers with text fields first, fallback to basic query
try {
    $stmt = $pdo->query("
        SELECT t.teacher_id, t.surname, t.middle_name, t.given_name, t.department_id, t.subject_id, 
               t.department AS department_text, t.strand AS strand_text, 
               d.department_name, s.subject_name
        FROM teachers t
        LEFT JOIN departments d ON d.department_id = t.department_id
        LEFT JOIN subjects s ON s.subject_id = t.subject_id
        ORDER BY t.surname, t.given_name
    ");
} catch (PDOException $e) {
    // Fallback query if text columns don't exist
    $stmt = $pdo->query("
        SELECT t.teacher_id, t.surname, t.middle_name, t.given_name, t.department_id, t.subject_id, 
               NULL AS department_text, NULL AS strand_text,
               d.department_name, s.subject_name
        FROM teachers t
        LEFT JOIN departments d ON d.department_id = t.department_id
        LEFT JOIN subjects s ON s.subject_id = t.subject_id
        ORDER BY t.surname, t.given_name
    ");
}
$rows = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Teachers</h1>
        <p class="text-muted mb-0">Manage teacher accounts and assignments</p>
    </div>
    <div>
        <a href="../../public/index.php" class="btn btn-outline-secondary me-2">← Back to Dashboard</a>
        <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Teacher</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (isset($_GET['created'])): ?>
            <div class="alert alert-success py-2">Teacher created.</div>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success py-2">Teacher updated.</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success py-2">Teacher deleted.</div>
        <?php endif; ?>
        <?php if (empty($rows)): ?>
            <p class="text-muted mb-0">No teachers yet. <a href="create.php">Add one</a>.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Subject handle</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?php echo (int)$r['teacher_id']; ?></td>
                                <td><?php echo htmlspecialchars($r['surname'] . ', ' . $r['given_name'] . (!empty($r['middle_name']) ? ' ' . $r['middle_name'] : '')); ?></td>
                                <td><?php echo htmlspecialchars(trim((string)($r['department_text'] ?? '')) !== '' ? trim($r['department_text']) : ($r['department_name'] ?? '-')); ?></td>
                                <td><?php echo htmlspecialchars(trim((string)($r['strand_text'] ?? '')) !== '' ? trim($r['strand_text']) : ($r['subject_name'] ?? '-')); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?php echo (int)$r['teacher_id']; ?>" class="btn btn-outline-primary">Edit</a>
                                        <a href="delete.php?id=<?php echo (int)$r['teacher_id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this teacher?');">Delete</a>
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
