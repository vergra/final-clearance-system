<?php
$baseUrl = '..';
$pageTitle = 'Department Details';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$department_id = (int)($_GET['id'] ?? 0);
if (!$department_id) {
    header('Location: index.php');
    exit;
}

// Get department info
$stmt = $pdo->prepare("SELECT * FROM departments WHERE department_id = ?");
$stmt->execute([$department_id]);
$department = $stmt->fetch();
if (!$department) {
    header('Location: index.php');
    exit;
}

// Get strands for this department with subject count
$stmt = $pdo->prepare("
    SELECT s.strand_id, s.strand_name,
           (SELECT COUNT(*) FROM subjects WHERE strand_id = s.strand_id) as subject_count
    FROM strands s
    WHERE s.department_id = ?
    ORDER BY s.strand_name
");
$stmt->execute([$department_id]);
$strands = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0"><?php echo htmlspecialchars($department['department_name']); ?></h1>
        <p class="text-muted mb-0">Manage strands and subjects for this department</p>
    </div>
    <div>
        <a href="index.php" class="btn btn-outline-secondary me-2">Back to Departments</a>
        <a href="create_strand.php?department_id=<?php echo $department_id; ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Strand</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Department Info</h6>
                <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($department['department_name']); ?></p>
                <p class="mb-0"><strong>Total Strands:</strong> <?php echo count($strands); ?></p>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_GET['strand_created'])): ?>
    <div class="alert alert-success py-2">Strand created.</div>
<?php endif; ?>
<?php if (isset($_GET['strand_updated'])): ?>
    <div class="alert alert-success py-2">Strand updated.</div>
<?php endif; ?>
<?php if (isset($_GET['strand_deleted'])): ?>
    <div class="alert alert-success py-2">Strand deleted.</div>
<?php endif; ?>

<?php if (empty($strands)): ?>
    <div class="text-center py-5">
        <i class="bi bi-folder text-muted" style="font-size: 3rem;"></i>
        <h5 class="mt-3">No strands yet</h5>
        <p class="text-muted">Add your first strand to get started.</p>
        <a href="create_strand.php?department_id=<?php echo $department_id; ?>" class="btn btn-primary">Add Strand</a>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($strands as $strand): ?>
            <div class="col-6 col-md-6 col-lg-4">
                <a href="view_strand.php?id=<?php echo $strand['strand_id']; ?>" class="text-decoration-none">
                    <div class="card h-100 border-primary admin-dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Strand</span>
                                <span class="badge bg-primary rounded-pill"><?php echo $strand['subject_count']; ?> subjects</span>
                            </div>
                            <h5 class="card-title mt-2"><?php echo htmlspecialchars($strand['strand_name']); ?></h5>
                            <p class="card-text small text-muted mb-0">Click to manage subjects</p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
