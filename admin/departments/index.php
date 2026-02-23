<?php
$baseUrl = '..';
$pageTitle = 'Departments';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$stmt = $pdo->query("SELECT * FROM departments ORDER BY department_name");
$rows = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Departments</h1>
        <p class="text-muted mb-0">Manage departments, strands, and subjects</p>
    </div>
    <div>
        <a href="../../public/index.php" class="btn btn-outline-secondary me-2">← Back to Dashboard</a>
        <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Department</a>
    </div>
</div>

<?php if (isset($_GET['created'])): ?>
    <div class="alert alert-success py-2">Department created.</div>
<?php endif; ?>
<?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success py-2">Department updated.</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success py-2">Department deleted.</div>
<?php endif; ?>

<?php 
// Get strand count for each department
$departmentCounts = [];
foreach ($rows as $dept) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as strand_count FROM strands WHERE department_id = ?");
    $stmt->execute([$dept['department_id']]);
    $count = $stmt->fetch();
    $departmentCounts[$dept['department_id']] = $count['strand_count'];
}
?>

<?php if (empty($rows)): ?>
    <div class="text-center py-5">
        <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
        <h5 class="mt-3">No departments yet</h5>
        <p class="text-muted">Add your first department to get started.</p>
        <a href="create.php" class="btn btn-primary">Add Department</a>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($rows as $r): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-primary admin-dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Department</span>
                                    <span class="badge bg-primary rounded-pill"><?php echo $departmentCounts[$r['department_id']]; ?> strands</span>
                                </div>
                                <h5 class="card-title mt-2"><?php echo htmlspecialchars($r['department_name']); ?></h5>
                                <p class="card-text small text-muted mb-0">Click to manage strands and subjects</p>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="view.php?id=<?php echo (int)$r['department_id']; ?>">
                                        <i class="bi bi-eye me-2"></i>View Details
                                    </a></li>
                                    <li><a class="dropdown-item" href="edit.php?id=<?php echo (int)$r['department_id']; ?>">
                                        <i class="bi bi-pencil me-2"></i>Edit Department
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="delete.php?id=<?php echo (int)$r['department_id']; ?>" onclick="return confirm('Delete <?php echo htmlspecialchars($r['department_name']); ?> department?');">
                                        <i class="bi bi-trash me-2"></i>Delete Department
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <a href="view.php?id=<?php echo (int)$r['department_id']; ?>" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-folder2-open me-1"></i>Manage Strands & Subjects
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
