<?php
$baseUrl = '..';
$pageTitle = 'Add Strand';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$department_id = (int)($_GET['department_id'] ?? 0);
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

$error = '';
$strand_name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $strand_name = trim($_POST['strand_name'] ?? '');
    if ($strand_name === '') {
        $error = 'Strand name is required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO strands (strand_name, department_id) VALUES (?, ?)");
            $stmt->execute([$strand_name, $department_id]);
            header('Location: view.php?id=' . $department_id . '&strand_created=1');
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to save strand.';
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Add Strand to <?php echo htmlspecialchars($department['department_name']); ?></h1>
    <a href="view.php?id=<?php echo $department_id; ?>" class="btn btn-outline-secondary">Back to Department</a>
</div>

<div class="card" style="max-width: 400px;">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <input type="hidden" name="department_id" value="<?php echo $department_id; ?>">
            <div class="mb-3">
                <label for="department_name" class="form-label">Department</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($department['department_name']); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="strand_name" class="form-label">Strand name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="strand_name" name="strand_name" value="<?php echo htmlspecialchars($strand_name); ?>" placeholder="e.g. STEM, ABM, HUMSS, GAS, ICT, or any custom strand" required>
                <small class="text-muted">Enter any strand name you want (e.g., STEM, ABM, HUMSS, GAS, ICT, or create your own)</small>
            </div>
            <button type="submit" class="btn btn-primary">Save Strand</button>
            <a href="view.php?id=<?php echo $department_id; ?>" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
