<?php
$baseUrl = '..';
$pageTitle = 'Add Requirement';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$depts = $pdo->query("SELECT department_id, department_name FROM departments ORDER BY department_name")->fetchAll();

$error = '';
$requirement_name = '';
$department_id = (int)($_POST['department_id'] ?? ($depts[0]['department_id'] ?? 0));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requirement_name = trim($_POST['requirement_name'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);
    if ($requirement_name === '') {
        $error = 'Requirement name is required.';
    } elseif (!$department_id) {
        $error = 'Please select a department.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO requirements (requirement_name, department_id) VALUES (?, ?)");
            $stmt->execute([$requirement_name, $department_id]);
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
    <h1 class="h2 mb-0">Add Requirement</h1>
    <a href="index.php" class="btn btn-outline-secondary">Back to list</a>
</div>

<div class="card" style="max-width: 450px;">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="requirement_name" class="form-label">Requirement name</label>
                <input type="text" class="form-control" id="requirement_name" name="requirement_name" value="<?php echo htmlspecialchars($requirement_name); ?>" placeholder="e.g. Library Fines" required>
            </div>
            <div class="mb-3">
                <label for="department_id" class="form-label">Department</label>
                <select class="form-select" id="department_id" name="department_id" required>
                    <?php foreach ($depts as $d): ?>
                        <option value="<?php echo (int)$d['department_id']; ?>" <?php echo $department_id == $d['department_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['department_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="index.php" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
