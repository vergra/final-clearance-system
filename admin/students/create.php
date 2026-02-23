<?php
$baseUrl = '..';
$pageTitle = 'Add Student';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$blocks = $pdo->query("SELECT block_code, block_name FROM blocks ORDER BY block_code")->fetchAll();

$error = '';
$lrn = $surname = $middle_name = $given_name = $strand = '';
$block_code = $_POST['block_code'] ?? ($blocks[0]['block_code'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lrn = trim($_POST['lrn'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $given_name = trim($_POST['given_name'] ?? '');
    $strand = trim($_POST['strand'] ?? '');
    $block_code = trim($_POST['block_code'] ?? '');
    if ($lrn === '' || $surname === '' || $given_name === '' || $strand === '' || $block_code === '') {
        $error = 'LRN, surname, given name, strand, and block are required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO students (lrn, surname, middle_name, given_name, strand, block_code) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$lrn, $surname, $middle_name ?: null, $given_name, $strand, $block_code]);
            header('Location: index.php?created=1');
            exit;
        } catch (PDOException $e) {
            $error = $e->getCode() == 23000 ? 'That LRN already exists.' : 'Failed to save.';
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Add Student</h1>
    <a href="index.php" class="btn btn-outline-secondary">Back to list</a>
</div>

<div class="card" style="max-width: 500px;">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="lrn" class="form-label">LRN (Learner Reference Number)</label>
                <input type="text" class="form-control" id="lrn" name="lrn" value="<?php echo htmlspecialchars($lrn); ?>" required>
            </div>
            <div class="mb-3">
                <label for="surname" class="form-label">Surname</label>
                <input type="text" class="form-control" id="surname" name="surname" value="<?php echo htmlspecialchars($surname); ?>" required>
            </div>
            <div class="mb-3">
                <label for="given_name" class="form-label">Given name</label>
                <input type="text" class="form-control" id="given_name" name="given_name" value="<?php echo htmlspecialchars($given_name); ?>" required>
            </div>
            <div class="mb-3">
                <label for="middle_name" class="form-label">Middle name</label>
                <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($middle_name); ?>">
            </div>
            <div class="mb-3">
                <label for="strand" class="form-label">Strand</label>
                <input type="text" class="form-control" id="strand" name="strand" value="<?php echo htmlspecialchars($strand); ?>" placeholder="e.g. STEM, HUMSS" required>
            </div>
            <div class="mb-3">
                <label for="block_code" class="form-label">Block</label>
                <select class="form-select" id="block_code" name="block_code" required>
                    <?php foreach ($blocks as $b): ?>
                        <option value="<?php echo htmlspecialchars($b['block_code']); ?>" <?php echo $block_code === $b['block_code'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['block_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="index.php" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
