<?php
$baseUrl = '..';
$pageTitle = 'Teacher Subject Assignments';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

// Get current school year
$schoolYearStmt = $pdo->query("SELECT school_year_id, year_label FROM school_year ORDER BY year_label DESC LIMIT 1");
$currentSchoolYear = $schoolYearStmt->fetch();

// Get all teachers
$teachers = $pdo->query("SELECT teacher_id, surname, given_name, middle_name FROM teachers ORDER BY surname, given_name")->fetchAll();

// Get all subjects
$subjects = $pdo->query("SELECT subject_id, subject_name, department_name FROM subjects s JOIN departments d ON d.department_id = s.department_id ORDER BY subject_name")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = (int)($_POST['teacher_id'] ?? 0);
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    
    if ($teacher_id && $subject_id && $currentSchoolYear) {
        try {
            if ($action === 'assign') {
                // Check if assignment already exists
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM teacher_subject WHERE teacher_id = ? AND subject_id = ? AND school_year_id = ?");
                $checkStmt->execute([$teacher_id, $subject_id, $currentSchoolYear['school_year_id']]);
                
                if ($checkStmt->fetchColumn() == 0) {
                    $stmt = $pdo->prepare("INSERT INTO teacher_subject (teacher_id, subject_id, school_year_id) VALUES (?, ?, ?)");
                    $stmt->execute([$teacher_id, $subject_id, $currentSchoolYear['school_year_id']]);
                    $success = "Teacher assigned to subject successfully.";
                } else {
                    $error = "This teacher is already assigned to this subject.";
                }
            } elseif ($action === 'remove') {
                $stmt = $pdo->prepare("DELETE FROM teacher_subject WHERE teacher_id = ? AND subject_id = ? AND school_year_id = ?");
                $stmt->execute([$teacher_id, $subject_id, $currentSchoolYear['school_year_id']]);
                $success = "Teacher assignment removed successfully.";
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get current assignments
$assignments = [];
if ($currentSchoolYear) {
    $stmt = $pdo->prepare("
        SELECT ts.teacher_subject_id, t.teacher_id, t.surname, t.given_name, 
               s.subject_id, s.subject_name, d.department_name
        FROM teacher_subject ts
        JOIN teachers t ON t.teacher_id = ts.teacher_id
        JOIN subjects s ON s.subject_id = ts.subject_id
        JOIN departments d ON d.department_id = s.department_id
        WHERE ts.school_year_id = ?
        ORDER BY t.surname, t.given_name, s.subject_name
    ");
    $stmt->execute([$currentSchoolYear['school_year_id']]);
    $assignments = $stmt->fetchAll();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Teacher Subject Assignments</h1>
    <a href="index.php" class="btn btn-outline-secondary">← Back to Teachers</a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Assign Subject to Teacher</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="assign">
                    
                    <div class="mb-3">
                        <label for="teacher_id" class="form-label">Teacher</label>
                        <select name="teacher_id" id="teacher_id" class="form-select" required>
                            <option value="">Select Teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['teacher_id']; ?>">
                                    <?php echo htmlspecialchars($teacher['surname'] . ', ' . $teacher['given_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject_id" class="form-label">Subject</label>
                        <select name="subject_id" id="subject_id" class="form-select" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['subject_id']; ?>">
                                    <?php echo htmlspecialchars($subject['subject_name'] . ' (' . $subject['department_name'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Assign Subject
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    Current Assignments 
                    <small class="text-muted">(<?php echo htmlspecialchars($currentSchoolYear['year_label']); ?>)</small>
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($assignments)): ?>
                    <p class="text-muted">No teacher-subject assignments found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Teacher</th>
                                    <th>Subject</th>
                                    <th>Department</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignments as $assignment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($assignment['surname'] . ', ' . $assignment['given_name']); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['subject_name']); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['department_name']); ?></td>
                                        <td>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="teacher_id" value="<?php echo $assignment['teacher_id']; ?>">
                                                <input type="hidden" name="subject_id" value="<?php echo $assignment['subject_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Remove this assignment?')">
                                                    <i class="bi bi-trash"></i> Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
