<?php
$pageTitle = 'My Clearance';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');
$pdo = getDB();
$user = getCurrentUser();
$lrn = $user['reference_id'];

$stmt = $pdo->prepare("
    SELECT c.clearance_id, c.requirement_id, c.teacher_id, c.school_year_id,
           c.status, c.date_submitted, c.date_cleared, c.remarks,
           r.requirement_name, t.surname AS t_surname, t.given_name AS t_given, sy.year_label
    FROM clearance_status c
    JOIN requirements r ON r.requirement_id = c.requirement_id
    JOIN teachers t ON t.teacher_id = c.teacher_id
    JOIN school_year sy ON sy.school_year_id = c.school_year_id
    WHERE c.lrn = ?
    ORDER BY sy.year_label DESC, r.requirement_name
");
$stmt->execute([$lrn]);
$rows = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">My Clearance</h1>
    <a href="request_clearance.php" class="btn btn-success"><i class="bi bi-file-earmark-plus me-1"></i> Request New Clearance</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($rows)): ?>
            <div class="text-center py-4">
                <i class="bi bi-file-earmark text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3">No Clearance Records</h5>
                <p class="text-muted">You haven't requested any clearance yet.</p>
                <a href="request_clearance.php" class="btn btn-success">Request Your First Clearance</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Requirement</th>
                            <th>School Year</th>
                            <th>Teacher</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Cleared</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['requirement_name']); ?></td>
                                <td><?php echo htmlspecialchars($r['year_label']); ?></td>
                                <td><?php echo htmlspecialchars($r['t_surname'] . ', ' . $r['t_given']); ?></td>
                                <td>
                                    <?php
                                    $status = $r['status'];
                                    $class = $status === 'Approved' ? 'success' : ($status === 'Declined' ? 'danger' : 'warning text-dark');
                                    ?>
                                    <span class="badge bg-<?php echo $class; ?>"><?php echo htmlspecialchars($status); ?></span>
                                </td>
                                <td><?php echo $r['date_submitted'] ? htmlspecialchars($r['date_submitted']) : '-'; ?></td>
                                <td><?php echo $r['date_cleared'] ? htmlspecialchars($r['date_cleared']) : '-'; ?></td>
                                <td><?php echo $r['remarks'] ? htmlspecialchars($r['remarks']) : '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
