<?php
$baseUrl = '..';
$pageTitle = 'Strand Details';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$strand_id = (int)($_GET['id'] ?? 0);
if (!$strand_id) {
    header('Location: index.php');
    exit;
}

// Get strand info with department
$stmt = $pdo->prepare("SELECT s.*, d.department_name FROM strands s JOIN departments d ON s.department_id = d.department_id WHERE s.strand_id = ?");
$stmt->execute([$strand_id]);
$strand = $stmt->fetch();
if (!$strand) {
    header('Location: index.php');
    exit;
}

// Get subjects for this strand
$stmt = $pdo->prepare("SELECT * FROM subjects WHERE strand_id = ? ORDER BY subject_name");
$stmt->execute([$strand_id]);
$subjects = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0"><?php echo htmlspecialchars($strand['strand_name']); ?></h1>
        <p class="text-muted mb-0"><?php echo htmlspecialchars($strand['department_name']); ?> Department</p>
    </div>
    <div>
        <a href="view.php?id=<?php echo $strand['department_id']; ?>" class="btn btn-outline-secondary me-2">← Back to Department</a>
        <a href="add_subject.php?strand_id=<?php echo $strand_id; ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Subject</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Strand Info</h6>
                <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($strand['strand_name']); ?></p>
                <p class="mb-1"><strong>Department:</strong> <?php echo htmlspecialchars($strand['department_name']); ?></p>
                <p class="mb-0"><strong>Total Subjects:</strong> <?php echo count($subjects); ?></p>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_GET['subject_created'])): ?>
    <div class="alert alert-success py-2">Subject created.</div>
<?php endif; ?>
<?php if (isset($_GET['subject_updated'])): ?>
    <div class="alert alert-success py-2">Subject updated.</div>
<?php endif; ?>
<?php if (isset($_GET['subject_deleted'])): ?>
    <div class="alert alert-success py-2">Subject deleted.</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger py-2"><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></div>
<?php endif; ?>
<?php if (isset($_GET['strand_created'])): ?>
    <div class="alert alert-success py-2">Strand created. Add your first subject to see it in the department list.</div>
<?php endif; ?>

<?php if (empty($subjects)): ?>
    <div class="text-center py-5">
        <i class="bi bi-book text-muted" style="font-size: 3rem;"></i>
        <h5 class="mt-3">No subjects yet</h5>
        <p class="text-muted">Add your first subject to get started.</p>
        <a href="add_subject.php?strand_id=<?php echo $strand_id; ?>" class="btn btn-primary">Add Subject</a>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <h6 class="card-title mb-3">Subjects List</h6>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Subject Name</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $subject): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit_subject.php?id=<?php echo $subject['subject_id']; ?>&strand_id=<?php echo $strand_id; ?>" class="btn btn-outline-primary">Edit</a>
                                        <a href="delete_subject.php?id=<?php echo $subject['subject_id']; ?>&strand_id=<?php echo $strand_id; ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this subject?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
