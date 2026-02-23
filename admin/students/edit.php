<?php
$baseUrl = '..';
$pageTitle = 'Edit Student';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$lrn = trim($_GET['lrn'] ?? '');
if ($lrn === '') {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM students WHERE lrn = ?");
$stmt->execute([$lrn]);
$row = $stmt->fetch();
if (!$row) {
    header('Location: index.php');
    exit;
}

$blocks = $pdo->query("SELECT block_code, block_name FROM blocks ORDER BY block_code")->fetchAll();
$error = '';
$surname = $row['surname'];
$middle_name = $row['middle_name'] ?? '';
$given_name = $row['given_name'];
$strand = $row['strand'];
$block_code = $row['block_code'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $surname = trim($_POST['surname'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $given_name = trim($_POST['given_name'] ?? '');
    $strand = trim($_POST['strand'] ?? '');
    $block_code = trim($_POST['block_code'] ?? '');
    if ($surname === '' || $given_name === '' || $strand === '' || $block_code === '') {
        $error = 'Surname, given name, strand, and block are required.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE students SET surname = ?, middle_name = ?, given_name = ?, strand = ?, block_code = ? WHERE lrn = ?");
            $stmt->execute([$surname, $middle_name ?: null, $given_name, $strand, $block_code, $lrn]);
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
    <h1 class="h2 mb-0">Edit Student</h1>
    <a href="index.php" class="btn btn-outline-secondary">Back to list</a>
</div>

<div class="card" style="max-width: 500px;">
    <div class="card-body">
        <p class="text-muted small">LRN: <strong><?php echo htmlspecialchars($lrn); ?></strong></p>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
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
                <input type="text" class="form-control" id="strand" name="strand" value="<?php echo htmlspecialchars($strand); ?>" required>
            </div>
            <div class="mb-3">
                <label for="block_code" class="form-label">Block</label>
                <select class="form-select" id="block_code" name="block_code" required>
                    <?php foreach ($blocks as $b): ?>
                        <option value="<?php echo htmlspecialchars($b['block_code']); ?>" <?php echo $block_code === $b['block_code'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['block_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="index.php" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
