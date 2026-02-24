<?php
$baseUrl = '../..';
$pageTitle = 'Edit Clearance';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin', 'teacher']);
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM clearance_status WHERE clearance_id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    header('Location: index.php');
    exit;
}

$students = $pdo->query("SELECT lrn, surname, given_name FROM students ORDER BY surname, given_name")->fetchAll();
$requirements = $pdo->query("SELECT requirement_id, requirement_name FROM requirements ORDER BY requirement_name")->fetchAll();
$teachers = $pdo->query("SELECT teacher_id, surname, given_name FROM teachers ORDER BY surname, given_name")->fetchAll();
$years = $pdo->query("SELECT school_year_id, year_label FROM school_year ORDER BY year_label DESC")->fetchAll();

$error = '';
$lrn = $row['lrn'];
$requirement_id = (int)$row['requirement_id'];
$teacher_id = (int)$row['teacher_id'];
$school_year_id = (int)$row['school_year_id'];
$status = $row['status'];
$date_submitted = $row['date_submitted'] ?? '';
$date_cleared = $row['date_cleared'] ?? '';
$remarks = $row['remarks'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lrn = trim($_POST['lrn'] ?? '');
    $requirement_id = (int)($_POST['requirement_id'] ?? 0);
    $teacher_id = (int)($_POST['teacher_id'] ?? 0);
    $school_year_id = (int)($_POST['school_year_id'] ?? 0);
    $status = $_POST['status'] ?? 'Pending';
    $date_submitted = trim($_POST['date_submitted'] ?? '') ?: null;
    $date_cleared = trim($_POST['date_cleared'] ?? '') ?: null;
    $remarks = trim($_POST['remarks'] ?? '') ?: null;
    if (!$lrn || !$requirement_id || !$teacher_id || !$school_year_id) {
        $error = 'Student, requirement, teacher, and school year are required.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE clearance_status SET lrn = ?, requirement_id = ?, teacher_id = ?, school_year_id = ?, status = ?, date_submitted = ?, date_cleared = ?, remarks = ? WHERE clearance_id = ?");
            $stmt->execute([$lrn, $requirement_id, $teacher_id, $school_year_id, $status, $date_submitted, $date_cleared, $remarks, $id]);
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
    <h1 class="h2 mb-0">Edit Clearance Record</h1>
    <a href="index.php" class="btn btn-outline-secondary">Back to list</a>
</div>

<div class="card" style="max-width: 550px;">
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
                <label for="requirement_id" class="form-label">Requirement</label>
                <select class="form-select" id="requirement_id" name="requirement_id" required>
                    <?php foreach ($requirements as $req): ?>
                        <option value="<?php echo (int)$req['requirement_id']; ?>" <?php echo $requirement_id == $req['requirement_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($req['requirement_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="teacher_id" class="form-label">Verifying teacher</label>
                <select class="form-select" id="teacher_id" name="teacher_id" required>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?php echo (int)$t['teacher_id']; ?>" <?php echo $teacher_id == $t['teacher_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['surname'] . ', ' . $t['given_name']); ?></option>
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
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Approved" <?php echo $status === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="Declined" <?php echo $status === 'Declined' ? 'selected' : ''; ?>>Declined</option>
                </select>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="date_submitted" class="form-label">Date submitted</label>
                    <input type="date" class="form-control" id="date_submitted" name="date_submitted" value="<?php echo htmlspecialchars($date_submitted); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="date_cleared" class="form-label">Date cleared</label>
                    <input type="date" class="form-control" id="date_cleared" name="date_cleared" value="<?php echo htmlspecialchars($date_cleared); ?>">
                </div>
            </div>
            <div class="mb-3">
                <label for="remarks" class="form-label">Remarks</label>
                <textarea class="form-control" id="remarks" name="remarks" rows="2"><?php echo htmlspecialchars($remarks); ?></textarea>
            </div>
            <button type="submit" class="btn btn-success">Update</button>
            <a href="index.php" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
