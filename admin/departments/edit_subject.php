<?php
$baseUrl = '..';
$pageTitle = 'Edit Subject';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
$strand_id = (int)($_GET['strand_id'] ?? 0);
if (!$id || !$strand_id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM subjects WHERE subject_id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    header('Location: view_strand.php?id=' . $strand_id);
    exit;
}

// Ensure subject belongs to the strand we are viewing
if (!empty($row['strand_id']) && (int)$row['strand_id'] !== $strand_id) {
    header('Location: view_strand.php?id=' . $strand_id);
    exit;
}

$error = '';
$subject_name = $row['subject_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name'] ?? '');
    if ($subject_name === '') {
        $error = 'Subject name is required.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE subjects SET subject_name = ? WHERE subject_id = ?");
            $stmt->execute([$subject_name, $id]);
            header('Location: view_strand.php?id=' . $strand_id . '&subject_updated=1');
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to save.';
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Edit Subject</h1>
    <a href="view_strand.php?id=<?php echo $strand_id; ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Strand</a>
</div>

<div class="card" style="max-width: 450px;">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="subject_name" class="form-label">Subject name</label>
                <input type="text" class="form-control" id="subject_name" name="subject_name" value="<?php echo htmlspecialchars($subject_name); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="view_strand.php?id=<?php echo $strand_id; ?>" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
