<?php
$baseUrl = '..';
$pageTitle = 'Edit Department';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM departments WHERE department_id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    header('Location: index.php');
    exit;
}

$error = '';
$department_name = $row['department_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_name = trim($_POST['department_name'] ?? '');
    if ($department_name === '') {
        $error = 'Department name is required.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE departments SET department_name = ? WHERE department_id = ?");
            $stmt->execute([$department_name, $id]);
            header('Location: index.php?updated=1');
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to save.';
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Edit Department</h1>
    <div>
        <a href="../../public/index.php" class="btn btn-outline-secondary me-2">← Back to Home</a>
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
                <input type="text" class="form-control" id="department_name" name="department_name" value="<?php echo htmlspecialchars($department_name); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="index.php" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
