<?php
$baseUrl = '..';
$pageTitle = 'Student Enrollment';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$stmt = $pdo->query("
    SELECT ss.student_subject_id, ss.lrn, ss.subject_id, ss.school_year_id,
           s.given_name AS student_given, s.surname AS student_surname,
           sub.subject_name, sy.year_label
    FROM student_subject ss
    JOIN students s ON s.lrn = ss.lrn
    JOIN subjects sub ON sub.subject_id = ss.subject_id
    JOIN school_year sy ON sy.school_year_id = ss.school_year_id
    ORDER BY sy.year_label DESC, s.surname, sub.subject_name
");
$rows = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Student Enrollment (Subjects)</h1>
    <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Enroll Student</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (isset($_GET['created'])): ?>
            <div class="alert alert-success py-2">Enrollment added.</div>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success py-2">Enrollment updated.</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success py-2">Enrollment removed.</div>
        <?php endif; ?>
        <?php if (empty($rows)): ?>
            <p class="text-muted mb-0">No enrollments yet. <a href="create.php">Enroll a student</a>.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>School Year</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?php echo (int)$r['student_subject_id']; ?></td>
                                <td><?php echo htmlspecialchars($r['student_surname'] . ', ' . $r['student_given'] . ' (' . $r['lrn'] . ')'); ?></td>
                                <td><?php echo htmlspecialchars($r['subject_name']); ?></td>
                                <td><?php echo htmlspecialchars($r['year_label']); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?php echo (int)$r['student_subject_id']; ?>" class="btn btn-outline-primary">Edit</a>
                                        <a href="delete.php?id=<?php echo (int)$r['student_subject_id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Remove this enrollment?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
