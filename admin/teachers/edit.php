<?php
$baseUrl = '..';
$pageTitle = 'Edit Teacher';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM teachers WHERE teacher_id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    header('Location: index.php');
    exit;
}

$depts = $pdo->query("SELECT department_id, department_name FROM departments ORDER BY department_name")->fetchAll();
$subjects = $pdo->query("SELECT subject_id, subject_name, strand FROM subjects ORDER BY subject_name")->fetchAll();
$error = '';
$surname = $row['surname'];
$given_name = $row['given_name'];
$middle_name = isset($row['middle_name']) ? $row['middle_name'] : '';
$department_id = (int)$row['department_id'];
$subject_id = isset($row['subject_id']) && $row['subject_id'] !== null ? (int)$row['subject_id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $surname = trim($_POST['surname'] ?? '');
    $given_name = trim($_POST['given_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);
    $subject_id = isset($_POST['subject_id']) && $_POST['subject_id'] !== '' ? (int)$_POST['subject_id'] : null;
    if ($surname === '' || $given_name === '') {
        $error = 'Surname and given name are required.';
    } elseif (!$department_id) {
        $error = 'Please select a department.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE teachers SET surname = ?, middle_name = ?, given_name = ?, department_id = ?, subject_id = ? WHERE teacher_id = ?");
            $stmt->execute([$surname, $middle_name !== '' ? $middle_name : null, $given_name, $department_id, $subject_id, $id]);
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
    <h1 class="h2 mb-0">Edit Teacher</h1>
    <a href="index.php" class="btn btn-outline-secondary">Back to list</a>
</div>

<div class="card" style="max-width: 450px;">
    <div class="card-body">
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
                <label for="department_id" class="form-label">Department</label>
                <select class="form-select" id="department_id" name="department_id" required>
                    <?php foreach ($depts as $d): ?>
                        <option value="<?php echo (int)$d['department_id']; ?>" <?php echo $department_id == $d['department_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['department_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="subject_id" class="form-label">Subject handle</label>
                <select class="form-select" id="subject_id" name="subject_id">
                    <option value="">-- Optional --</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?php echo (int)$s['subject_id']; ?>" <?php echo $subject_id === (int)$s['subject_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['subject_name'] . ' (' . $s['strand'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="index.php" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
