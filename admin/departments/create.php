<?php
$baseUrl = '..';
$pageTitle = 'Add Department';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$error = '';
$department_name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_name = trim($_POST['department_name'] ?? '');
    if ($department_name === '') {
        $error = 'Department name is required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO departments (department_name) VALUES (?)");
            $stmt->execute([$department_name]);
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
    <h1 class="h2 mb-0">Add Department</h1>
    <div>
        <a href="../../public/index.php" class="btn btn-outline-secondary me-2">← Back to Dashboard</a>
        <a href="index.php" class="btn btn-outline-secondary">Back to Departments</a>
    </div>
</div>

<div class="card" style="max-width: 400px;">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="department_name" class="form-label">Department name</label>
                <input type="text" class="form-control" id="department_name" name="department_name" value="<?php echo htmlspecialchars($department_name); ?>" placeholder="e.g. Registrar" required>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="index.php" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
