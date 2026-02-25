<?php
$baseUrl = '..';
$pageTitle = 'Sign-up requests';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$stmt = $pdo->query("
    SELECT r.signup_request_id, r.lrn, r.requested_username, r.status, r.requested_at, r.reviewed_at, r.remarks,
           s.surname, s.given_name, s.middle_name, s.strand, s.block_code
    FROM signup_requests r
    JOIN students s ON s.lrn = r.lrn
    ORDER BY r.status ASC, r.requested_at DESC
");
$rows = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Signup Requests</h1>
        <p class="text-muted mb-0">Review and approve student/teacher registration requests</p>
    </div>
    <div>
        <a href="../../public/index.php" class="btn btn-outline-secondary">← Back to Home</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (isset($_GET['approved'])): ?>
            <div class="alert alert-success py-2">Sign-up request approved. The student can now log in.</div>
        <?php endif; ?>
        <?php if (isset($_GET['declined'])): ?>
            <div class="alert alert-info py-2">Sign-up request declined.</div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['signup_error'])): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($_SESSION['signup_error']); unset($_SESSION['signup_error']); ?></div>
        <?php endif; ?>
        <?php if (empty($rows)): ?>
            <p class="text-muted mb-0">No sign-up requests yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>LRN</th>
                            <th>Student name</th>
                            <th>Strand</th>
                            <th>Block</th>
                            <th>Requested username</th>
                            <th>Requested at</th>
                            <th>Status</th>
                            <th width="180">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['lrn']); ?></td>
                                <td><?php echo htmlspecialchars($r['surname'] . ', ' . $r['given_name'] . ($r['middle_name'] ? ' ' . $r['middle_name'] : '')); ?></td>
                                <td><?php echo htmlspecialchars($r['strand']); ?></td>
                                <td><?php echo htmlspecialchars($r['block_code']); ?></td>
                                <td><?php echo htmlspecialchars($r['requested_username']); ?></td>
                                <td><?php echo htmlspecialchars($r['requested_at']); ?></td>
                                <td>
                                    <?php
                                    $st = $r['status'];
                                    $badge = $st === 'approved' ? 'success' : ($st === 'declined' ? 'secondary' : 'warning text-dark');
                                    ?>
                                    <span class="badge bg-<?php echo $badge; ?>"><?php echo htmlspecialchars(ucfirst($st)); ?></span>
                                </td>
                                <td>
                                    <?php if ($r['status'] === 'pending'): ?>
                                        <div class="btn-group btn-group-sm">
                                            <a href="approve.php?id=<?php echo (int)$r['signup_request_id']; ?>" class="btn btn-success" onclick="return confirm('Approve this sign-up? The student will be able to log in.');">Approve</a>
                                            <a href="decline.php?id=<?php echo (int)$r['signup_request_id']; ?>" class="btn btn-outline-danger">Decline</a>
                                        </div>
                                    <?php else: ?>
                                        <?php if ($r['remarks']): ?>
                                            <span class="small text-muted" title="<?php echo htmlspecialchars($r['remarks']); ?>"><?php echo htmlspecialchars(substr($r['remarks'], 0, 20)); ?>…</span>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    <?php endif; ?>
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
