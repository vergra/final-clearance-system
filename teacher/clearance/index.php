<?php
$baseUrl = '../..';
$pageTitle = 'Clearance Status';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin', 'teacher']);
$pdo = getDB();

$stmt = $pdo->query("
    SELECT c.clearance_id, c.lrn, c.requirement_id, c.teacher_id, c.school_year_id,
           c.status, c.date_submitted, c.date_cleared, c.remarks,
           s.surname AS st_surname, s.given_name AS st_given,
           r.requirement_name, t.surname AS t_surname, t.given_name AS t_given, sy.year_label
    FROM clearance_status c
    JOIN students s ON s.lrn = c.lrn
    JOIN requirements r ON r.requirement_id = c.requirement_id
    JOIN teachers t ON t.teacher_id = c.teacher_id
    JOIN school_year sy ON sy.school_year_id = c.school_year_id
    ORDER BY c.clearance_id DESC
");
$rows = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Clearance Status</h1>
    <a href="create.php" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i> New Clearance Record</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (isset($_GET['created'])): ?>
            <div class="alert alert-success py-2">Clearance record created.</div>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success py-2">Clearance record updated.</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success py-2">Clearance record deleted.</div>
        <?php endif; ?>
        <?php if (empty($rows)): ?>
            <p class="text-muted mb-0">No clearance records yet. <a href="create.php">Add one</a>.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Requirement</th>
                            <th>Teacher</th>
                            <th>School Year</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Cleared</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?php echo (int)$r['clearance_id']; ?></td>
                                <td><?php echo htmlspecialchars($r['st_surname'] . ', ' . $r['st_given'] . ' (' . $r['lrn'] . ')'); ?></td>
                                <td><?php echo htmlspecialchars($r['requirement_name']); ?></td>
                                <td><?php echo htmlspecialchars($r['t_surname'] . ', ' . $r['t_given']); ?></td>
                                <td><?php echo htmlspecialchars($r['year_label']); ?></td>
                                <td>
                                    <?php
                                    $status = $r['status'];
                                    $class = $status === 'Approved' ? 'success' : ($status === 'Declined' ? 'danger' : 'warning text-dark');
                                    ?>
                                    <span class="badge bg-<?php echo $class; ?>"><?php echo htmlspecialchars($status); ?></span>
                                </td>
                                <td><?php echo $r['date_submitted'] ? htmlspecialchars($r['date_submitted']) : '-'; ?></td>
                                <td><?php echo $r['date_cleared'] ? htmlspecialchars($r['date_cleared']) : '-'; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?php echo (int)$r['clearance_id']; ?>" class="btn btn-outline-primary">Edit</a>
                                        <a href="delete.php?id=<?php echo (int)$r['clearance_id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this clearance record?');">Delete</a>
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
