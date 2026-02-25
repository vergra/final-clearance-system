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
    <div class="landing-page">
    <div class="row mb-4">
        <div class="col-12">
            <div class="text-center">
                <div class="mb-3">
                    <div class="bg-gradient-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-2" style="width: 80px; height: 80px;">
                        <i class="bi bi-check2-square" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <h1 class="h2 mb-2 fw-bold text-primary">Senior High School Clearance</h1>
                <p class="text-muted mb-3">Digitize and streamline the clearance process with our comprehensive management system</p>
            </div>
        </div>
    </div>
    <div class="row g-4 justify-content-center">
        <div class="col-md-6 col-lg-4">
            <a href="<?php echo $base; ?>/public/login.php?as=student" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-lg hover-lift">
                    <div class="card-body text-center py-4">
                        <div class="bg-primary bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-person-video3" style="font-size: 1.5rem;"></i>
                        </div>
                        <h5 class="card-title fw-bold">Student</h5>
                        <p class="card-text text-muted small">View your clearance status and manage compliance requirements</p>
                        <span class="btn btn-primary btn-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Log in as Student
                        </span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="<?php echo $base; ?>/public/login.php?as=teacher" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-lg hover-lift">
                    <div class="card-body text-center py-4">
                        <div class="bg-success bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-person-badge" style="font-size: 1.5rem;"></i>
                        </div>
                        <h5 class="card-title fw-bold">Teacher</h5>
                        <p class="card-text text-muted small">Review and approve clearances with compliance tracking</p>
                        <span class="btn btn-success btn-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Log in as Teacher
                        </span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="<?php echo $base; ?>/public/login.php?as=admin" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-lg hover-lift">
                    <div class="card-body text-center py-4">
                        <div class="bg-danger bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-gear" style="font-size: 1.5rem;"></i>
                        </div>
                        <h5 class="card-title fw-bold">Administrator</h5>
                        <p class="card-text text-muted small">Manage system settings and user accounts</p>
                        <span class="btn btn-danger btn-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Log in as Admin
                        </span>
                    </div>
                </div>
            </a>
        </div>
    </div>
    </div>
    <style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.bg-primary.bg-gradient {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
}

.bg-success.bg-gradient {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
}

.bg-danger.bg-gradient {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
}

.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15) !important;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-3px);
}

.badge {
    font-weight: 500;
    letter-spacing: 0.5px;
}

/* Optimize landing page for no scroll */
.landing-page {
    min-height: calc(100vh - 200px);
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.landing-page .row:first-child {
    margin-bottom: 2rem !important;
}

.landing-page .row:last-child {
    margin-bottom: 0 !important;
}

@media (min-height: 700px) {
    .landing-page {
        min-height: calc(100vh - 150px);
    }
}

/* Simple admin card hover effects */
.admin-card-simple {
    transition: all 0.2s ease;
}

.admin-card-simple:hover {
    background-color: #f8f9fa;
    border-color: #007bff;
}

.admin-card-simple:hover .text-muted {
    color: #6c757d !important;
}

.admin-card-simple:hover .text-primary {
    color: #0056b3 !important;
}
</style>

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

<div class="row mb-5">
    <div class="col-12">
        <div class="text-center">
            <h1 class="h2 mb-3 fw-bold text-primary">
                <?php if ($currentUser['role'] === 'student'): ?>
                    <i class="bi bi-person-video3 me-2"></i>Student Home Page
                <?php elseif ($currentUser['role'] === 'teacher'): ?>
                    <i class="bi bi-person-badge me-2"></i>Teacher Home Page
                <?php else: ?>
                    <i class="bi bi-gear me-2"></i>Admin Home Page
                <?php endif; ?>
            </h1>
            <p class="lead text-muted">
                <?php if ($currentUser['role'] === 'student'): ?>
                    View your clearance status and manage compliance requirements
                <?php elseif ($currentUser['role'] === 'teacher'): ?>
                    Review and approve clearance requests with compliance tracking
                <?php else: ?>
                    Manage and monitor the entire clearance system
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<?php if ($currentUser['role'] === 'student'): ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-lg">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <div class="bg-primary bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-person-video3" style="font-size: 2rem;"></i>
                    </div>
                </div>
                <h4 class="card-title fw-bold mb-3">Student Home</h4>
                <p class="card-text text-muted mb-4">Manage your clearance requests and track compliance requirements</p>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="<?php echo rtrim(WEB_BASE, '/'); ?>/student/my_clearance.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-clipboard-check me-2"></i> My Clearance
                    </a>
                    <a href="<?php echo rtrim(WEB_BASE, '/'); ?>/student/request_clearance.php" class="btn btn-success btn-lg">
                        <i class="bi bi-file-earmark-plus me-2"></i> Request Clearance
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php elseif ($currentUser['role'] === 'teacher'): ?>
<?php
// Get teacher's clearance data for homepage
$teacher_id = $currentUser['reference_id'];
$pdo = getDB();

// For teachers, only show their assigned clearance requests
$stmt = $pdo->prepare("
    SELECT c.clearance_id, c.lrn, c.requirement_id, c.teacher_id, c.school_year_id,
           c.status, c.date_submitted, c.date_cleared, c.remarks,
           s.surname AS st_surname, s.given_name AS st_given,
           r.requirement_name, t.surname AS t_surname, t.given_name AS t_given, sy.year_label
    FROM clearance_status c
    JOIN students s ON s.lrn = c.lrn
    JOIN requirements r ON r.requirement_id = c.requirement_id
    JOIN teachers t ON t.teacher_id = c.teacher_id
    JOIN school_year sy ON sy.school_year_id = c.school_year_id
    WHERE c.teacher_id = ?
    ORDER BY c.clearance_id DESC
");
$stmt->execute([$teacher_id]);
$clearanceRows = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Pending Clearance Requests</h5>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleView('all')">
                <i class="bi bi-list-ul me-1"></i> View All Records
            </button>
        </div>
        
        <?php if (empty($clearanceRows)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                    <h6 class="mt-3 text-muted">No clearance records yet</h6>
                    <p class="text-muted small">You don't have any clearance requests assigned to you.</p>
                </div>
            </div>
        <?php else: ?>
            <!-- Pending Requests Section (Default View) -->
            <div id="pendingSection">
                <div class="row">
                    <?php 
                    $pendingRequests = array_filter($clearanceRows, function($r) { return $r['status'] === 'Pending'; });
                    foreach ($pendingRequests as $r): 
                    ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-warning clickable-card" onclick="showStudentDetails(<?php echo (int)$r['clearance_id']; ?>)">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0"><?php echo htmlspecialchars($r['st_surname'] . ', ' . $r['st_given']); ?></h6>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    </div>
                                    <p class="card-text small text-muted mb-1">
                                        <i class="bi bi-person me-1"></i><?php echo htmlspecialchars($r['lrn']); ?>
                                    </p>
                                    <p class="card-text small text-muted mb-1">
                                        <i class="bi bi-book me-1"></i><?php echo htmlspecialchars($r['requirement_name']); ?>
                                    </p>
                                    <p class="card-text small text-muted mb-0">
                                        <i class="bi bi-calendar me-1"></i><?php echo $r['date_submitted'] ? htmlspecialchars($r['date_submitted']) : '-'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($pendingRequests)): ?>
                        <div class="col-12">
                            <div class="alert alert-info py-2">
                                <i class="bi bi-info-circle me-2"></i>No pending requests. All caught up!
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- All Records Section (Hidden by default) -->
            <div id="allRecordsSection" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>All Clearance Records</h6>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleView('pending')">
                        <i class="bi bi-clock-history me-1"></i> Back to Pending
                    </button>
                </div>
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Student</th>
                                        <th>Requirement</th>
                                        <th>School Year</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clearanceRows as $r): ?>
                                        <tr class="clickable-row" onclick="showStudentDetails(<?php echo (int)$r['clearance_id']; ?>)" style="cursor: pointer;">
                                            <td><?php echo (int)$r['clearance_id']; ?></td>
                                            <td><?php echo htmlspecialchars($r['st_surname'] . ', ' . $r['st_given'] . ' (' . $r['lrn'] . ')'); ?></td>
                                            <td><?php echo htmlspecialchars($r['requirement_name']); ?></td>
                                            <td><?php echo htmlspecialchars($r['year_label']); ?></td>
                                            <td>
                                                <?php
                                                $status = $r['status'];
                                                $class = $status === 'Approved' ? 'success' : ($status === 'Declined' ? 'danger' : 'warning text-dark');
                                                ?>
                                                <span class="badge bg-<?php echo $class; ?>"><?php echo htmlspecialchars($status); ?></span>
                                            </td>
                                            <td><?php echo $r['date_submitted'] ? htmlspecialchars($r['date_submitted']) : '-'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Student Details Modal -->
<div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="studentDetailsModalLabel">
                    <i class="bi bi-person-badge me-2"></i>Student Clearance Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="studentDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading student details...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="returnComplianceBtn" style="display: none;">
                    <i class="bi bi-arrow-return-left me-1"></i>Return for Compliance
                </button>
                <button type="button" class="btn btn-success" id="approveBtn" style="display: none;">
                    <i class="bi bi-check-circle me-1"></i>Approved
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Return for Compliance Modal -->
<div class="modal fade" id="returnComplianceModal" tabindex="-1" aria-labelledby="returnComplianceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="returnComplianceModalLabel">
                    <i class="bi bi-arrow-return-left me-2"></i>Return for Compliance
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="returnComplianceForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="complianceRemarks" class="form-label">
                            <strong>Requirements/Compliance Needed:</strong>
                        </label>
                        <textarea class="form-control" id="complianceRemarks" name="complianceRemarks" rows="4" 
                                  placeholder="Please specify what requirements or compliance the student needs to complete..." required></textarea>
                        <small class="text-muted">Be specific about what documents, actions, or requirements are needed.</small>
                    </div>
                    <input type="hidden" id="complianceClearanceId" name="clearance_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-return-left me-1"></i>Return for Compliance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.clickable-card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.clickable-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.clickable-card:active {
    transform: translateY(0);
}

.student-detail-card {
    border-left: 4px solid #0d6efd;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: #495057;
}

.detail-value {
    text-align: right;
}

.status-badge-custom {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: 500;
}

.clickable-row {
    transition: background-color 0.2s ease;
}

.clickable-row:hover {
    background-color: rgba(0, 123, 255, 0.1);
}

.clickable-row:active {
    background-color: rgba(0, 123, 255, 0.2);
}
</style>

<script>
function toggleView(view) {
    const pendingSection = document.getElementById('pendingSection');
    const allRecordsSection = document.getElementById('allRecordsSection');
    const viewAllBtn = document.querySelector('[onclick="toggleView(\'all\')"]');
    
    if (view === 'all') {
        pendingSection.style.display = 'none';
        allRecordsSection.style.display = 'block';
        viewAllBtn.style.display = 'none';
    } else {
        pendingSection.style.display = 'block';
        allRecordsSection.style.display = 'none';
        viewAllBtn.style.display = 'inline-flex';
    }
}

function showStudentDetails(clearanceId) {
    const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
    const content = document.getElementById('studentDetailsContent');
    const approveBtn = document.getElementById('approveBtn');
    const returnBtn = document.getElementById('returnComplianceBtn');
    
    // Show loading state
    content.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading student details...</p>
        </div>
    `;
    
    // Hide action buttons initially
    approveBtn.style.display = 'none';
    returnBtn.style.display = 'none';
    
    // Fetch student details
    fetch('<?php echo rtrim(WEB_BASE, '/'); ?>/teacher/clearance/get_student_details.php?id=' + clearanceId)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.error}
                    </div>
                `;
                return;
            }
            
            const statusClass = data.status === 'Approved' ? 'success' : 
                               data.status === 'Declined' ? 'danger' : 'warning text-dark';
            
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card student-detail-card mb-3">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="bi bi-person me-2"></i>Student Information
                                </h6>
                                <div class="detail-item">
                                    <span class="detail-label">Name:</span>
                                    <span class="detail-value">${data.student_name}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">LRN:</span>
                                    <span class="detail-value">${data.lrn}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Grade Block:</span>
                                    <span class="detail-value">${data.block_code}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Strand:</span>
                                    <span class="detail-value">${data.strand}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card student-detail-card mb-3">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="bi bi-file-text me-2"></i>Clearance Information
                                </h6>
                                <div class="detail-item">
                                    <span class="detail-label">Requirement:</span>
                                    <span class="detail-value">${data.requirement_name}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Subject:</span>
                                    <span class="detail-value">${data.subject_name}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">School Year:</span>
                                    <span class="detail-value">${data.year_label}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Status:</span>
                                    <span class="detail-value">
                                        <span class="badge bg-${statusClass} status-badge-custom">${data.status}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card student-detail-card">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="bi bi-calendar-check me-2"></i>Timeline & Remarks
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <span class="detail-label">Date Submitted:</span>
                                            <span class="detail-value">${data.date_submitted || '-'}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Date Cleared:</span>
                                            <span class="detail-value">${data.date_cleared || '-'}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <span class="detail-label">Remarks:</span>
                                            <span class="detail-value">${data.remarks || 'No remarks'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Show action buttons only for pending requests
            if (data.status === 'Pending') {
                approveBtn.style.display = 'inline-block';
                returnBtn.style.display = 'inline-block';
                
                // Set button actions
                approveBtn.onclick = function() {
                    updateClearanceStatus(clearanceId, 'Approved', '');
                };
                
                returnBtn.onclick = function() {
                    document.getElementById('complianceClearanceId').value = clearanceId;
                    document.getElementById('complianceRemarks').value = '';
                    const complianceModal = new bootstrap.Modal(document.getElementById('returnComplianceModal'));
                    complianceModal.show();
                };
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Failed to load student details. Please try again.
                </div>
            `;
        });
    
    modal.show();
}

function updateClearanceStatus(clearanceId, status, remarks) {
    const formData = new FormData();
    formData.append('clearance_id', clearanceId);
    formData.append('status', status);
    formData.append('remarks', remarks);
    
    fetch('<?php echo rtrim(WEB_BASE, '/'); ?>/teacher/clearance/update_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modals
            bootstrap.Modal.getInstance(document.getElementById('studentDetailsModal')).hide();
            bootstrap.Modal.getInstance(document.getElementById('returnComplianceModal'))?.hide();
            
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>
                Clearance ${status.toLowerCase()} successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container').prepend(alertDiv);
            
            // Reload page to show updated status
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            alert('Error: ' + (data.error || 'Failed to update status'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update clearance status. Please try again.');
    });
}

// Handle return compliance form submission
document.getElementById('returnComplianceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const clearanceId = document.getElementById('complianceClearanceId').value;
    const remarks = document.getElementById('complianceRemarks').value;
    
    updateClearanceStatus(clearanceId, 'Declined', remarks);
});
</script>
<?php else: ?>
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-4">
        <a href="<?php echo rtrim(WEB_BASE, '/'); ?>/admin/school_years/index.php" class="text-decoration-none">
            <div class="card h-100 border admin-card-simple">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">School Years</h6>
                            <h3 class="card-title mb-0 text-primary"><?php echo $counts['school_year']; ?></h3>
                        </div>
                        <i class="bi bi-calendar3 text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="<?php echo rtrim(WEB_BASE, '/'); ?>/admin/departments/index.php" class="text-decoration-none">
            <div class="card h-100 border admin-card-simple">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Departments</h6>
                            <h3 class="card-title mb-0 text-primary"><?php echo $counts['departments']; ?></h3>
                        </div>
                        <i class="bi bi-building text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="<?php echo rtrim(WEB_BASE, '/'); ?>/admin/blocks/index.php" class="text-decoration-none">
            <div class="card h-100 border admin-card-simple">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Blocks</h6>
                            <h3 class="card-title mb-0 text-primary"><?php echo $counts['blocks']; ?></h3>
                        </div>
                        <i class="bi bi-grid-3x3 text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="<?php echo rtrim(WEB_BASE, '/'); ?>/admin/teachers/index.php" class="text-decoration-none">
            <div class="card h-100 border admin-card-simple">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Teachers</h6>
                            <h3 class="card-title mb-0 text-primary"><?php echo $counts['teachers']; ?></h3>
                        </div>
                        <i class="bi bi-people text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="<?php echo rtrim(WEB_BASE, '/'); ?>/admin/students/index.php" class="text-decoration-none">
            <div class="card h-100 border admin-card-simple">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Students</h6>
                            <h3 class="card-title mb-0 text-primary"><?php echo $counts['students']; ?></h3>
                        </div>
                        <i class="bi bi-mortarboard text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
