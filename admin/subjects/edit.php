<?php
$baseUrl = '..';
$pageTitle = 'Edit Subject';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM subjects WHERE subject_id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    header('Location: index.php');
    exit;
}

$depts = $pdo->query("SELECT department_id, department_name FROM departments ORDER BY department_name")->fetchAll();
$error = '';
$subject_name = $row['subject_name'];
$strand = $row['strand'];
$department_id = (int)$row['department_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name'] ?? '');
    $strand = trim($_POST['strand'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);
    if ($subject_name === '' || $strand === '') {
        $error = 'Subject name and strand are required.';
    } elseif (!$department_id) {
        $error = 'Please select a department.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE subjects SET subject_name = ?, strand = ?, department_id = ? WHERE subject_id = ?");
            $stmt->execute([$subject_name, $strand, $department_id, $id]);
            $returnUrl = $returnTo === 'departments' ? '../departments/index.php?updated=1' : 'index.php?updated=1';
            header('Location: ' . $returnUrl);
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to save.';
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Edit Subject</h1>
    <?php if ($returnTo === 'departments'): ?>
        <a href="../departments/index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    <?php else: ?>
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    <?php endif; ?>
</div>

<div class="card" style="max-width: 450px;">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="subject_name" class="form-label">Subject name</label>
                <input type="text" class="form-control" id="subject_name" name="subject_name" value="<?php echo htmlspecialchars($subject_name); ?>" required>
            </div>
            <div class="mb-3">
                <label for="strand" class="form-label">Strand</label>
                <input type="text" class="form-control" id="strand" name="strand" value="<?php echo htmlspecialchars($strand); ?>" required>
            </div>
            <div class="mb-3">
                <label for="department_id" class="form-label">Department</label>
                <select class="form-select" id="department_id" name="department_id" required>
                    <?php foreach ($depts as $d): ?>
                        <option value="<?php echo (int)$d['department_id']; ?>" <?php echo $department_id == $d['department_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['department_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <?php if ($returnTo === 'departments'): ?>
                <a href="../departments/index.php" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Back</a>
            <?php else: ?>
                <a href="index.php" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Back</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
