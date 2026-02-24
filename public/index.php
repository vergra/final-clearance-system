<?php
// Start session FIRST before anything else with proper cookie path
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/student_clearance/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

$pageTitle = 'Home';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
$currentUser = getCurrentUser();

// Not logged in: show landing page with Student / Teacher / Admin login buttons
if ($currentUser === null) {
    $base = rtrim(WEB_BASE, '/');
    require_once __DIR__ . '/../includes/header.php';
    ?>
    <div class="row mb-4">
        <div class="col text-center">
            <h1 class="h2">Gradline: Senior High School Clearance</h1>
            <p class="text-muted">Digitize and streamline the clearance process. Choose how you want to sign in.</p>
        </div>
    </div>
    <div class="row g-4 justify-content-center py-4">
        <div class="col-md-6 col-lg-4">
            <a href="<?php echo $base; ?>/public/login.php?as=student" class="text-decoration-none">
                <div class="card h-100 border-primary shadow-sm hover-lift">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-person-video3 text-primary" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3">Student</h5>
                        <p class="card-text text-muted small">View your clearance status</p>
                        <span class="btn btn-primary mt-2">Log in as Student</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="<?php echo $base; ?>/public/login.php?as=teacher" class="text-decoration-none">
                <div class="card h-100 border-success shadow-sm hover-lift">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-person-badge text-success" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3">Teacher</h5>
                        <p class="card-text text-muted small">Review and approve clearances</p>
                        <span class="btn btn-success mt-2">Log in as Teacher</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="<?php echo $base; ?>/public/login.php?as=admin" class="text-decoration-none">
                <div class="card h-100 border-secondary shadow-sm hover-lift">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-gear-wide-connected text-secondary" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3">Admin</h5>
                        <p class="card-text text-muted small">Manage data and user accounts</p>
                        <span class="btn btn-secondary mt-2">Log in as Admin</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    <?php
    exit;
}

$pdo = getDB();

// Counts for admin dashboard (only when admin)
$counts = [];
if ($currentUser['role'] === 'admin') {
    $tables = ['school_year' => 'School Years', 'departments' => 'Departments', 'blocks' => 'Blocks', 'teachers' => 'Teachers', 'subjects' => 'Subjects', 'students' => 'Students'];
    foreach (array_keys($tables) as $t) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $t");
            $counts[$t] = (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            $counts[$t] = 0;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h1 class="h2">Gradline: Senior High School Clearance</h1>
        <?php if ($currentUser['role'] === 'student'): ?>
            <p class="text-muted">View your clearance status and requirements.</p>
        <?php elseif ($currentUser['role'] === 'teacher'): ?>
            <p class="text-muted">Review and approve clearance requests for your department.</p>
        <?php else: ?>
            <p class="text-muted">Digitize and streamline the clearance process. Track status online; verify and approve requests efficiently.</p>
        <?php endif; ?>
    </div>
</div>

<?php if ($currentUser['role'] === 'student'): ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Student Dashboard</h5>
                <p class="card-text">Manage your clearance requests and view status.</p>
                <div class="d-flex gap-2">
                    <a href="<?php echo rtrim(WEB_BASE, '/'); ?>/student/my_clearance.php" class="btn btn-primary"><i class="bi bi-check2-square me-1"></i> My Clearance</a>
                    <a href="<?php echo rtrim(WEB_BASE, '/'); ?>/student/request_clearance.php" class="btn btn-success"><i class="bi bi-file-earmark-plus me-1"></i> Request Clearance</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php elseif ($currentUser['role'] === 'teacher'): ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Teacher Dashboard</h5>
                <p class="card-text">View and manage clearance records you are responsible for.</p>
                <a href="<?php echo rtrim(WEB_BASE, '/'); ?>/teacher/clearance/index.php" class="btn btn-primary"><i class="bi bi-list-ul me-1"></i> Clearance</a>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row g-3">
    <div class="col-md-6 col-lg-3">
        <a href="<?php echo rtrim(WEB_BASE, '/'); ?>/admin/school_years/index.php" class="text-decoration-none">
            <div class="card h-100 border-primary admin-dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">School Years</span>
                        <span class="badge bg-primary rounded-pill"><?php echo $counts['school_year']; ?></span>
                    </div>
                    <h5 class="card-title mt-2">School Years</h5>
                    <p class="card-text small text-muted mb-0">Manage academic years</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="<?php echo rtrim(WEB_BASE, '/'); ?>/admin/departments/index.php" class="text-decoration-none">
            <div class="card h-100 border-primary admin-dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Departments</span>
                        <span class="badge bg-primary rounded-pill"><?php echo $counts['departments']; ?></span>
                    </div>
                    <h5 class="card-title mt-2">Departments</h5>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="<?php echo rtrim(WEB_BASE, '/'); ?>/admin/blocks/index.php" class="text-decoration-none">
            <div class="card h-100 border-primary admin-dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Blocks</span>
                        <span class="badge bg-primary rounded-pill"><?php echo $counts['blocks']; ?></span>
                    </div>
                    <h5 class="card-title mt-2">Blocks</h5>
                    <p class="card-text small text-muted mb-0">Sections / blocks</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="<?php echo rtrim(WEB_BASE, '/'); ?>/admin/teachers/index.php" class="text-decoration-none">
            <div class="card h-100 border-primary admin-dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Teachers</span>
                        <span class="badge bg-primary rounded-pill"><?php echo $counts['teachers']; ?></span>
                    </div>
                    <h5 class="card-title mt-2">Teachers</h5>
                    <p class="card-text small text-muted mb-0">Authorized personnel</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="<?php echo rtrim(WEB_BASE, '/'); ?>/admin/students/index.php" class="text-decoration-none">
            <div class="card h-100 border-primary admin-dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Students</span>
                        <span class="badge bg-primary rounded-pill"><?php echo $counts['students']; ?></span>
                    </div>
                    <h5 class="card-title mt-2">Students</h5>
                    <p class="card-text small text-muted mb-0">Student records (LRN)</p>
                </div>
            </div>
        </a>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
