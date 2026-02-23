<?php
$baseUrl = '..';
$pageTitle = 'Add Block';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$error = '';
$block_code = '';
$block_name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $block_code = trim($_POST['block_code'] ?? '');
    $block_name = trim($_POST['block_name'] ?? '');
    if ($block_code === '' || $block_name === '') {
        $error = 'Block code and name are required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO blocks (block_code, block_name) VALUES (?, ?)");
            $stmt->execute([$block_code, $block_name]);
            header('Location: index.php?created=1');
            exit;
        } catch (PDOException $e) {
            $error = $e->getCode() == 23000 ? 'That block code already exists.' : 'Failed to save.';
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Add Block</h1>
    <a href="index.php" class="btn btn-outline-secondary">Back to list</a>
</div>

<div class="card" style="max-width: 400px;">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="block_code" class="form-label">Block code</label>
                <input type="text" class="form-control" id="block_code" name="block_code" value="<?php echo htmlspecialchars($block_code); ?>" placeholder="e.g. 11-A" required>
            </div>
            <div class="mb-3">
                <label for="block_name" class="form-label">Block name</label>
                <input type="text" class="form-control" id="block_name" name="block_name" value="<?php echo htmlspecialchars($block_name); ?>" placeholder="e.g. Grade 11 Section A" required>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="index.php" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
