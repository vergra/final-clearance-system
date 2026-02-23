<?php
$baseUrl = '..';
$pageTitle = 'Edit Enrollment';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM student_subject WHERE student_subject_id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    header('Location: index.php');
    exit;
}

$students = $pdo->query("SELECT lrn, surname, given_name FROM students ORDER BY surname, given_name")->fetchAll();
$subjects = $pdo->query("SELECT subject_id, subject_name FROM subjects ORDER BY subject_name")->fetchAll();
$years = $pdo->query("SELECT school_year_id, year_label FROM school_year ORDER BY year_label DESC")->fetchAll();

$error = '';
$lrn = $row['lrn'];
$subject_id = (int)$row['subject_id'];
$school_year_id = (int)$row['school_year_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lrn = trim($_POST['lrn'] ?? '');
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $school_year_id = (int)($_POST['school_year_id'] ?? 0);
    if (!$lrn || !$subject_id || !$school_year_id) {
        $error = 'Please select student, subject, and school year.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE student_subject SET lrn = ?, subject_id = ?, school_year_id = ? WHERE student_subject_id = ?");
            $stmt->execute([$lrn, $subject_id, $school_year_id, $id]);
            header('Location: index.php?updated=1');
            exit;
        } catch (PDOException $e) {
            $error = $e->getCode() == 23000 ? 'Duplicate enrollment.' : 'Failed to save.';
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Edit Enrollment</h1>
    <a href="index.php" class="btn btn-outline-secondary">Back to list</a>
</div>

<div class="card" style="max-width: 500px;">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="lrn" class="form-label">Student</label>
                <select class="form-select" id="lrn" name="lrn" required>
                    <?php foreach ($students as $s): ?>
                        <option value="<?php echo htmlspecialchars($s['lrn']); ?>" <?php echo $lrn === $s['lrn'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['surname'] . ', ' . $s['given_name'] . ' (' . $s['lrn'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="subject_id" class="form-label">Subject</label>
                <select class="form-select" id="subject_id" name="subject_id" required>
                    <?php foreach ($subjects as $sub): ?>
                        <option value="<?php echo (int)$sub['subject_id']; ?>" <?php echo $subject_id == $sub['subject_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($sub['subject_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="school_year_id" class="form-label">School Year</label>
                <select class="form-select" id="school_year_id" name="school_year_id" required>
                    <?php foreach ($years as $y): ?>
                        <option value="<?php echo (int)$y['school_year_id']; ?>" <?php echo $school_year_id == $y['school_year_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($y['year_label']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="index.php" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
