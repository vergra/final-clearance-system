<?php
$baseUrl = '..';
$pageTitle = 'Edit School Year';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM school_year WHERE school_year_id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    header('Location: index.php');
    exit;
}

$error = '';
$year_label = $row['year_label'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $year_label = trim($_POST['year_label'] ?? '');
    if ($year_label === '') {
        $error = 'Year label is required.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE school_year SET year_label = ? WHERE school_year_id = ?");
            $stmt->execute([$year_label, $id]);
            header('Location: index.php?updated=1');
            exit;
        } catch (PDOException $e) {
            $error = $e->getCode() == 23000 ? 'That year label already exists.' : 'Failed to save.';
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Edit School Year</h1>
    <a href="index.php" class="btn btn-outline-secondary">Back to list</a>
</div>

<div class="card" style="max-width: 400px;">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="year_label" class="form-label">Year label</label>
                <input type="text" class="form-control" id="year_label" name="year_label" value="<?php echo htmlspecialchars($year_label); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="index.php" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
