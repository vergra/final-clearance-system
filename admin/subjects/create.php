<?php
$baseUrl = '..';
$pageTitle = 'Add Subject';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$depts = $pdo->query("SELECT department_id, department_name FROM departments ORDER BY department_name")->fetchAll();

$error = '';
$subject_name = $strand = '';
$department_id = (int)($_POST['department_id'] ?? ($depts[0]['department_id'] ?? 0));
// Pre-fill strand from query parameter if provided
$strandPreFilled = false;
$isAddingStrand = false; // True when accessed via "Add strand" button (has department_id but no strand)
if (empty($_POST['strand']) && isset($_GET['strand'])) {
    $strand = trim($_GET['strand']);
    $strandPreFilled = true;
} elseif (!isset($_GET['strand']) && isset($_GET['department_id'])) {
    // Accessed via "Add strand" button - only department_id provided, no strand
    $isAddingStrand = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name'] ?? '');
    $strand = trim($_POST['strand'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);
    $isAddingStrandMode = isset($_POST['is_adding_strand']) && $_POST['is_adding_strand'] === '1';
    
    // When adding strand only, use placeholder subject name
    if ($isAddingStrandMode && $subject_name === '') {
        $subject_name = 'New Subject - ' . $strand;
    }
    
    if ($subject_name === '' || $strand === '') {
        $error = 'Subject name and strand are required.';
    } elseif (!$department_id) {
        $error = 'Please select a department.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO subjects (subject_name, strand, department_id) VALUES (?, ?, ?)");
            $stmt->execute([$subject_name, $strand, $department_id]);
            header('Location: index.php?created=1');
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to save.';
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0"><?php echo $isAddingStrand ? 'Add Strand' : 'Add Subject'; ?></h1>
    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card" style="max-width: 450px;">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <?php if ($isAddingStrand): ?>
                <input type="hidden" name="is_adding_strand" value="1">
                <input type="hidden" name="subject_name" value="">
            <?php else: ?>
                <div class="mb-3">
                    <label for="subject_name" class="form-label">Subject name</label>
                    <input type="text" class="form-control" id="subject_name" name="subject_name" value="<?php echo htmlspecialchars($subject_name); ?>" placeholder="e.g. General Mathematics" required>
                </div>
            <?php endif; ?>
            <div class="mb-3">
                <label for="strand" class="form-label">Strand</label>
                <input type="text" class="form-control" id="strand" name="strand" value="<?php echo htmlspecialchars($strand); ?>" placeholder="e.g. STEM, HUMSS" <?php echo $strandPreFilled ? 'readonly' : ''; ?> required>
                <?php if ($strandPreFilled): ?>
                    <small class="text-muted">Pre-filled from selected strand. This subject will be added to this strand.</small>
                <?php elseif ($isAddingStrand): ?>
                    <small class="text-muted">Enter the strand name. You can add subjects to this strand later using the "Add subject" button.</small>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="department_id" class="form-label">Department</label>
                <select class="form-select" id="department_id" name="department_id" required>
                    <?php foreach ($depts as $d): ?>
                        <option value="<?php echo (int)$d['department_id']; ?>" <?php echo $department_id == $d['department_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['department_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $isAddingStrand ? 'Create Strand' : 'Save'; ?></button>
            <a href="index.php" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
