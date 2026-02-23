<?php
$baseUrl = '..';
$pageTitle = 'Add Subject';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$strand_id = (int)($_GET['strand_id'] ?? 0);
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

$error = '';
$subject_name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name'] ?? '');
    if ($subject_name === '') {
        $error = 'Subject name is required.';
    } elseif (empty($strand['department_id']) || empty($strand['strand_name'])) {
        $error = 'Invalid strand/department. Please go back and try again.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO subjects (subject_name, strand, department_id) VALUES (?, ?, ?)");
            $stmt->execute([$subject_name, (string)$strand['strand_name'], (int)$strand['department_id']]);
            header('Location: view_strand.php?id=' . $strand_id . '&subject_created=1');
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to save subject. Error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Add Subject</h1>
        <p class="text-muted mb-0"><?php echo htmlspecialchars($strand['strand_name']); ?> → <?php echo htmlspecialchars($strand['department_name']); ?></p>
    </div>
    <a href="view_strand.php?id=<?php echo $strand_id; ?>" class="btn btn-outline-secondary">← Back to Strand</a>
</div>

<div class="card" style="max-width: 500px;">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="mb-3">
            <label class="form-label"><strong>Department:</strong></label>
            <p class="form-control-plaintext"><?php echo htmlspecialchars($strand['department_name']); ?></p>
        </div>
        
        <div class="mb-3">
            <label class="form-label"><strong>Strand:</strong></label>
            <p class="form-control-plaintext"><?php echo htmlspecialchars($strand['strand_name']); ?></p>
        </div>
        
        <form method="post" action="">
            <div class="mb-3">
                <label for="subject_name" class="form-label">Subject name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="subject_name" name="subject_name" value="<?php echo htmlspecialchars($subject_name); ?>" placeholder="e.g. Mathematics 11, English 12, Physics, Chemistry, or any subject" required>
                <small class="text-muted">Enter any subject name you want (e.g., Mathematics 11, English 12, Physics, Chemistry, etc.)</small>
            </div>
            <button type="submit" class="btn btn-primary">Add Subject</button>
            <a href="view_strand.php?id=<?php echo $strand_id; ?>" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
