<?php
$pageTitle = 'My Clearance';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');
$pdo = getDB();
$user = getCurrentUser();
$lrn = $user['reference_id'];

// Get student info
$stmt = $pdo->prepare("
    SELECT s.lrn, s.surname, s.given_name, s.middle_name, s.block_code,
           d.department_name, st.strand_name, sy.year_label
    FROM students s
    LEFT JOIN departments d ON d.department_name LIKE '%Senior%' OR d.department_name = 'Senior High School'
    LEFT JOIN strands st ON st.strand_name = s.strand
    LEFT JOIN school_year sy ON sy.year_label LIKE '%2025%' OR sy.school_year_id = (SELECT MAX(school_year_id) FROM school_year)
    WHERE s.lrn = ?
");
$stmt->execute([$lrn]);
$student = $stmt->fetch();

// Add date_returned column if it doesn't exist
try {
    $pdo->exec("ALTER TABLE clearance_status ADD COLUMN date_returned DATE NULL AFTER date_cleared");
} catch (PDOException $e) {
    // Column already exists, ignore error
}

// Get clearance requests grouped by school year and department
$stmt = $pdo->prepare("
    SELECT c.clearance_id, c.teacher_id, c.subject_id, c.school_year_id, c.request_group_id,
           c.status, c.date_submitted, c.date_cleared, c.remarks,
           t.surname AS t_surname, t.given_name AS t_given, 
           sub.subject_name, d.department_name, st.strand_name, sy.year_label
    FROM clearance_status c
    JOIN teachers t ON t.teacher_id = c.teacher_id
    JOIN subjects sub ON sub.subject_id = c.subject_id
    JOIN departments d ON d.department_id = 1
    JOIN strands st ON st.strand_id = 1
    JOIN school_year sy ON sy.school_year_id = c.school_year_id
    JOIN students s ON s.lrn = c.lrn
    WHERE c.lrn = ?
    ORDER BY sy.year_label DESC, c.date_submitted DESC
");
$stmt->execute([$lrn]);
$clearances = $stmt->fetchAll();

// Group clearances by school year + request_group_id (separate "forms")
$groupedClearances = [];
foreach ($clearances as $clearance) {
    $syId = (int)$clearance['school_year_id'];
    $submittedKey = $clearance['request_group_id'] ?: ($clearance['date_submitted'] ?: '0000-00-00');

    if (!isset($groupedClearances[$syId])) {
        $groupedClearances[$syId] = [
            'year_label' => $clearance['year_label'],
            'department_name' => $clearance['department_name'],
            'strand_name' => $clearance['strand_name'],
            'requests' => []
        ];
    }

    if (!isset($groupedClearances[$syId]['requests'][$submittedKey])) {
        $groupedClearances[$syId]['requests'][$submittedKey] = [
            'date_submitted' => $clearance['date_submitted'],
            'request_group_id' => $clearance['request_group_id'],
            'clearances' => []
        ];
    }

    $groupedClearances[$syId]['requests'][$submittedKey]['clearances'][] = $clearance;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">My Clearance</h1>
    <div>
        <a href="../public/index.php" class="btn btn-outline-secondary me-2">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <a href="request_clearance.php" class="btn btn-success"><i class="bi bi-file-earmark-plus me-1"></i> Request New Clearance</a>
        <button onclick="location.reload()" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh Status
        </button>
    </div>
</div>

<?php if (empty($groupedClearances)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-file-earmark text-muted" style="font-size: 4rem;"></i>
            <h5 class="mt-3">No Clearance Records</h5>
            <p class="text-muted">You haven't requested any clearance yet.</p>
            <a href="request_clearance.php" class="btn btn-success">Request Your First Clearance</a>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($groupedClearances as $schoolYearId => $yearData): ?>
        <?php
        $requests = $yearData['requests'];
        ksort($requests);
        $requests = array_reverse($requests, true);
        ?>
        <div class="mb-5">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><?php echo htmlspecialchars($yearData['year_label']); ?> - <?php echo htmlspecialchars($yearData['department_name']); ?></h5>
            </div>

            <?php foreach ($requests as $submittedKey => $requestData): ?>
                <?php if (isset($_GET['created'])): ?>
                    <div class="alert alert-success py-2">Clearance request created.</div>
                <?php endif; ?>
                <?php if (isset($_GET['updated'])): ?>
                    <div class="alert alert-success py-2">Clearance request updated successfully! Your return for compliance has been resubmitted and is now pending review.</div>
                <?php endif; ?>
                <?php if (isset($_GET['resubmitted'])): ?>
                    <div class="alert alert-success py-2">Clearance request updated successfully! Your return for compliance has been resubmitted and is now pending review.</div>
                <?php endif; ?>

                <div class="text-center">
                    <div class="bond-paper d-inline-block">
                        <div class="header">
                            <div class="title">Student Clearance Form</div>
                            <div class="subtitle">Senior High School Clearance</div>
                            <div class="subtitle">School Year: <?php echo htmlspecialchars($yearData['year_label']); ?></div>
                        </div>

                        <div class="student-details">
                            <div class="details-row">
                                <div class="detail-item">
                                    <label>Name:</label>
                                    <span><?php echo htmlspecialchars($student['surname'] . ', ' . $student['given_name'] . ' ' . $student['middle_name']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Grade Block:</label>
                                    <span><?php echo htmlspecialchars($student['block_code']); ?></span>
                                </div>
                            </div>
                            <div class="details-row">
                                <div class="detail-item">
                                    <label>LRN:</label>
                                    <span><?php echo htmlspecialchars($lrn); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Strand:</label>
                                    <span><?php echo htmlspecialchars($yearData['strand_name']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="subjects-section">
                            <div class="section-title">SUBJECTS AND TEACHERS</div>
                            <div class="subjects-list">
                                <?php foreach ($requestData['clearances'] as $clearance): ?>
                                    <div class="subject-item">
                                        <div class="subject-info">
                                            <div class="teacher-section">
                                                <span class="teacher-name"><?php echo htmlspecialchars($clearance['t_surname'] . ', ' . $clearance['t_given']); ?></span>
                                                <div class="teacher-divider"></div>
                                            </div>
                                            <span class="subject-name"><?php echo htmlspecialchars($clearance['subject_name']); ?></span>
                                            <?php if ($clearance['status'] === 'Approved'): ?>
                                                <div class="approved-indicator">
                                                    <i class="bi bi-check-circle-fill text-success"></i>
                                                    <small class="text-success d-block">Cleared on <?php echo htmlspecialchars($clearance['date_cleared']); ?></small>
                                                </div>
                                            <?php elseif ($clearance['status'] === 'Declined' && !empty($clearance['remarks'])): ?>
                                                <div class="compliance-needed-indicator">
                                                    <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                                                    <small class="text-warning d-block">Return for Compliance</small>
                                                    <button type="button" class="btn btn-outline-warning btn-sm mt-2" onclick="showComplianceDetails(<?php echo (int)$clearance['clearance_id']; ?>)">
                                                        <i class="bi bi-eye me-1"></i>View Requirements
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <span class="status-badge status-<?php echo strtolower($clearance['status']); ?>"><?php echo htmlspecialchars($clearance['status']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="signature-section">
                            <div class="signature-row">
                            </div>
                            <div class="signature-row">
                                <div class="signature-box">
                                    <div class="signature-label">Guidance Signature</div>
                                    <div class="signature-line"></div>
                                    <div class="name-label">Over Printed Name</div>
                                </div>
                                <div class="signature-box">
                                    <div class="signature-label">Principal Signature</div>
                                    <div class="signature-line"></div>
                                    <div class="name-label">Over Printed Name</div>
                                </div>
                            </div>
                            <div class="signature-row">
                                <div class="signature-box">
                                    <div class="signature-label">Registrar Signature</div>
                                    <div class="signature-line"></div>
                                    <div class="name-label">Over Printed Name</div>
                                </div>
                                <div class="signature-box">
                                    <div class="signature-label">Adviser Signature</div>
                                    <div class="signature-line"></div>
                                    <div class="name-label">
                                        <?php 
                                        $clearedDates = array_filter($requestData['clearances'], function($c) { return $c['date_cleared']; });
                                        if (!empty($clearedDates)) {
                                            echo date('F j, Y', strtotime(max(array_column($clearedDates, 'date_cleared'))));
                                        } else {
                                            echo 'Over printed Name';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3 mb-5">
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Print Clearance Form
                        </button>
                        <?php if (!empty($requestData['request_group_id'])): ?>
                            <a href="delete_clearance_request.php?request_group_id=<?php echo urlencode((string)$requestData['request_group_id']); ?>" class="btn btn-outline-danger ms-2" onclick="return confirm('Delete this clearance request form? This will remove all subjects/teachers in this form.');">
                                <i class="bi bi-trash me-1"></i> Delete Form
                            </a>
                        <?php else: ?>
                            <a href="delete_clearance_request.php?school_year_id=<?php echo (int)$schoolYearId; ?>&date_submitted=<?php echo urlencode((string)$requestData['date_submitted']); ?>" class="btn btn-outline-danger ms-2" onclick="return confirm('Delete this clearance request form? This will remove all subjects/teachers in this form.');">
                                <i class="bi bi-trash me-1"></i> Delete Form
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<style>
.bond-paper {
    background: white;
    width: 8.5in;
    min-height: 11in;
    padding: 1in;
    margin: 20px 0;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    font-family: 'Times New Roman', serif;
    font-size: 12pt;
    line-height: 1.4;
    text-align: left;
}

.bond-paper .header {
    text-align: center;
    margin-bottom: 30px;
}

.bond-paper .title {
    font-size: 18pt;
    font-weight: bold;
    margin-bottom: 5px;
}

.bond-paper .subtitle {
    font-size: 12pt;
    margin-bottom: 3px;
}

.bond-paper .student-details {
    margin-bottom: 20px;
}

.bond-paper .details-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.bond-paper .detail-item {
    flex: 1;
}

.bond-paper .detail-item:first-child {
    margin-right: 20px;
}

.bond-paper .detail-item label {
    font-weight: bold;
    margin-right: 10px;
}

.bond-paper .subjects-section {
    margin-bottom: 20px;
}

.bond-paper .section-title {
    font-weight: bold;
    font-size: 14pt;
    margin-bottom: 10px;
    border-bottom: 1px solid #333;
    padding-bottom: 5px;
    text-align: center;
}

.bond-paper .subjects-list {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    min-height: 100px;
}

.bond-paper .subject-item {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 12px;
    font-size: 12px;
    min-height: 90px;
}

.bond-paper .subject-info {
    display: flex;
    flex-direction: column;
    align-items: center !important;
    justify-content: center;
    gap: 2px;
    flex: 1;
}

.teacher-section {
    margin-bottom: 8px;
}

.teacher-divider {
    height: 1px;
    background: #666;
    margin: 4px 0;
    width: 100%;
}

.teacher-name {
    font-weight: bold;
    font-size: 0.9rem;
    color: #333;
    text-align: center;
    display: block;
}

.subject-name {
    font-size: 0.85rem;
    color: #555;
    display: block;
    margin-top: 4px;
    text-align: center;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: bold;
    text-transform: uppercase;
    display: block;
    margin: 8px auto 0;
    text-align: center;
}

.status-pending {
    background: #ffc107;
    color: #000;
}

.status-approved {
    background: #28a745;
    color: white;
    position: relative;
}

.status-approved::before {
    content: '✓ ';
    font-weight: bold;
}

.status-declined {
    background: #dc3545;
    color: white;
}

.approved-indicator {
    text-align: center;
    margin-top: 8px;
    padding: 4px;
    border-radius: 4px;
    background: rgba(40, 167, 69, 0.1);
}

.approved-indicator i {
    font-size: 1.2rem;
}

.approved-indicator small {
    font-size: 0.75rem;
    margin-top: 2px;
}

.compliance-needed-indicator {
    text-align: center;
    margin-top: 8px;
    padding: 8px;
    border-radius: 4px;
    background: rgba(255, 193, 7, 0.1);
    border: 1px solid rgba(255, 193, 7, 0.3);
}

.compliance-needed-indicator i {
    font-size: 1.2rem;
}

.compliance-needed-indicator small {
    font-size: 0.75rem;
    margin-top: 2px;
    font-weight: 600;
}

.bond-paper .remarks-section {
    margin-bottom: 25px;
}

.bond-paper .remarks-box {
    padding: 15px;
    min-height: 80px;
}

.bond-paper .remarks-content {
    font-size: 12px;
    min-height: 50px;
}

.bond-paper .signature-section {
    margin-bottom: 25px;
}

.bond-paper .signature-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
    margin-bottom: 20px;
}

.bond-paper .signature-box {
    text-align: center;
}

.bond-paper .signature-label {
    font-size: 11px;
    font-weight: bold;
    margin-bottom: 5px;
}

.bond-paper .signature-line {
    border-bottom: 1px solid #333;
    margin-bottom: 5px;
    height: 30px;
}

.bond-paper .name-label {
    font-size: 10px;
    color: #666;
    font-style: italic;
}

.bond-paper .footer {
    text-align: center;
    border-top: 1px solid #333;
    padding-top: 15px;
    margin-top: 30px;
}

.bond-paper .footer-note {
    font-size: 11px;
    color: #666;
    margin-bottom: 5px;
}

@media (max-width: 768px) {
    .bond-paper {
        padding: 20px;
        margin: 10px;
    }
    
    .bond-paper .details-row {
        flex-direction: column;
        margin-bottom: 15px;
    }
    
    .bond-paper .detail-item:first-child {
        margin-right: 0;
        margin-bottom: 10px;
    }
    
    .bond-paper .signature-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .bond-paper .subjects-list {
        grid-template-columns: 1fr;
    }
}

@media print {
    .card-header, .btn, .d-flex, header, footer {
        display: none !important;
    }
    .bond-paper {
        box-shadow: none;
        margin: 0;
        padding: 1in;
    }
    body {
        background: white;
    }
}
</style>

<!-- Compliance Details Modal -->
<div class="modal fade" id="complianceDetailsModal" tabindex="-1" aria-labelledby="complianceDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="complianceDetailsModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Return for Compliance Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="complianceDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading compliance details...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" onclick="requestNewClearance()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Request Again
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showComplianceDetails(clearanceId) {
    const modal = new bootstrap.Modal(document.getElementById('complianceDetailsModal'));
    const content = document.getElementById('complianceDetailsContent');
    
    // Show loading state
    content.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-warning" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading compliance details...</p>
        </div>
    `;
    
    // Fetch compliance details
    fetch('get_compliance_details.php?id=' + clearanceId)
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
            
            content.innerHTML = `
                <div class="alert alert-warning border border-warning">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-exclamation-triangle-fill me-3" style="font-size: 1.5rem;"></i>
                        <div>
                            <h6 class="alert-heading mb-2">Requirements Needed</h6>
                            <p class="mb-0">${data.requirements}</p>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <small class="text-muted">Teacher:</small>
                        <p class="mb-1"><strong>${data.teacher_name}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Subject:</small>
                        <p class="mb-1"><strong>${data.subject_name}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Date Returned:</small>
                        <p class="mb-1"><strong>${data.date_returned || data.date_cleared || 'Not available'}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Requirement:</small>
                        <p class="mb-1"><strong>${data.requirement_name}</strong></p>
                    </div>
                </div>
                
                <div class="mt-3 p-3 bg-light rounded">
                    <h6 class="text-muted mb-2">Next Steps:</h6>
                    <ol class="mb-0 small">
                        <li>Complete the requirements listed above</li>
                        <li>Prepare necessary documents</li>
                        <li>Click "Request Again" to automatically resubmit to the same teacher</li>
                    </ol>
                </div>
            `;
            
            // Update the request again button to pass the clearance ID
            document.querySelector('[onclick="requestNewClearance()"]').setAttribute('onclick', `requestNewClearance(${data.clearance_id})`);
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Failed to load compliance details. Please try again.
                </div>
            `;
        });
    
    modal.show();
}

function requestNewClearance(clearanceId = null) {
    if (clearanceId) {
        window.location.href = 'resubmit_clearance.php?id=' + clearanceId;
    } else {
        window.location.href = 'request_clearance.php';
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
