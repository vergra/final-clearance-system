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
$strand_id = (int)($_GET['strand_id'] ?? 0);
$strand_name = '';

// Pre-fill strand info if strand_id is provided
if ($strand_id) {
    $stmt = $pdo->prepare("SELECT s.strand_name, s.department_id FROM strands s WHERE s.strand_id = ?");
    $stmt->execute([$strand_id]);
    $strandInfo = $stmt->fetch();
    if ($strandInfo) {
        $strand = $strandInfo['strand_name'];
        $department_id = $strandInfo['department_id'];
        $strand_name = $strand;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name'] ?? '');
    $strand = trim($_POST['strand'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);
    $strand_id = (int)($_POST['strand_id'] ?? 0);
    
    if ($subject_name === '' || $strand === '') {
        $error = 'Subject name and strand are required.';
    } elseif (!$department_id) {
        $error = 'Please select a department.';
    } else {
        try {
            // Get strand_id if not provided
            if (!$strand_id) {
                $stmt = $pdo->prepare("SELECT strand_id FROM strands WHERE strand_name = ? AND department_id = ?");
                $stmt->execute([$strand, $department_id]);
                $strandRow = $stmt->fetch();
                if ($strandRow) {
                    $strand_id = $strandRow['strand_id'];
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO subjects (subject_name, strand, strand_id, department_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$subject_name, $strand, $strand_id ?: null, $department_id]);
            
            if ($strand_id) {
                header('Location: ../departments/view_strand.php?id=' . $strand_id . '&subject_created=1');
            } else {
                header('Location: index.php?created=1');
            }
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to save.';
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Add Subject</h1>
    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card" style="max-width: 450px;">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="subject_name" class="form-label">Subject name</label>
                <input type="text" class="form-control" id="subject_name" name="subject_name" value="<?php echo htmlspecialchars($subject_name); ?>" placeholder="e.g. General Mathematics" required>
            </div>
            <div class="mb-3">
                <label for="strand" class="form-label">Strand</label>
                <input type="text" class="form-control" id="strand" name="strand" value="<?php echo htmlspecialchars($strand); ?>" placeholder="e.g. STEM, HUMSS" <?php echo $strand_id ? 'readonly' : ''; ?> required>
                <?php if ($strand_id): ?>
                    <input type="hidden" name="strand_id" value="<?php echo $strand_id; ?>">
                    <small class="text-muted">Pre-filled from selected strand. This subject will be added to this strand.</small>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="department_id" class="form-label">Department</label>
                <select class="form-select" id="department_id" name="department_id" <?php echo $strand_id ? 'disabled' : ''; ?> required>
                    <?php foreach ($depts as $d): ?>
                        <option value="<?php echo (int)$d['department_id']; ?>" <?php echo $department_id == $d['department_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['department_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($strand_id): ?>
                    <input type="hidden" name="department_id" value="<?php echo $department_id; ?>">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="index.php" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
