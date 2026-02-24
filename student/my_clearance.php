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

// Get clearance requests grouped by school year and department
$stmt = $pdo->prepare("
    SELECT c.clearance_id, c.teacher_id, c.subject_id, c.school_year_id,
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

// Group clearances by school year
$groupedClearances = [];
foreach ($clearances as $clearance) {
    $groupedClearances[$clearance['school_year_id']]['year_label'] = $clearance['year_label'];
    $groupedClearances[$clearance['school_year_id']]['department_name'] = $clearance['department_name'];
    $groupedClearances[$clearance['school_year_id']]['strand_name'] = $clearance['strand_name'];
    $groupedClearances[$clearance['school_year_id']]['clearances'][] = $clearance;
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
        <div class="mb-5 text-center">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><?php echo htmlspecialchars($yearData['year_label']); ?> - <?php echo htmlspecialchars($yearData['department_name']); ?></h5>
            </div>
            
            <div class="bond-paper d-inline-block">
                <div class="header">
                    <div class="title">Student Clearance Form</div>
                    <div class="subtitle">Gradline Senior High School</div>
                    <div class="subtitle">School Year: <?php echo htmlspecialchars($yearData['year_label']); ?></div>
                </div>
                
                <div class="student-details">
                    <div class="details-row">
                        <div class="detail-item">
                            <label>Name:</label>
                            <span><?php echo htmlspecialchars($student['surname'] . ', ' . $student['given_name'] . ' ' . $student['middle_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Grade & Section:</label>
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
                        <?php foreach ($yearData['clearances'] as $clearance): ?>
                            <div class="subject-item">
                                <div class="subject-info">
                                    <div class="teacher-section">
                                        <span class="teacher-name"><?php echo htmlspecialchars($clearance['t_surname'] . ', ' . $clearance['t_given']); ?></span>
                                        <div class="teacher-divider"></div>
                                    </div>
                                    <span class="subject-name"><?php echo htmlspecialchars($clearance['subject_name']); ?></span>
                                    <span class="status-badge status-<?php echo strtolower($clearance['status']); ?>"><?php echo htmlspecialchars($clearance['status']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="remarks-section">
                    <div class="section-title">REMARKS</div>
                    <div class="remarks-box">
                        <div class="remarks-content">
                            <?php 
                            $remarks = [];
                            foreach ($yearData['clearances'] as $clearance) {
                                if (!empty($clearance['remarks'])) {
                                    $remarks[] = htmlspecialchars($clearance['subject_name'] . ': ' . $clearance['remarks']);
                                }
                            }
                            echo !empty($remarks) ? implode('<br>', $remarks) : 'No remarks yet.';
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="signature-section">
                    <div class="signature-row">
                        <div class="signature-box">
                            <div class="signature-label">Student Signature</div>
                            <div class="signature-line"></div>
                            <div class="name-label"><?php echo htmlspecialchars($student['surname'] . ', ' . $student['given_name']); ?></div>
                        </div>
                        <div class="signature-box">
                            <div class="signature-label">Date Submitted</div>
                            <div class="signature-line"></div>
                            <div class="name-label"><?php echo date('F j, Y', strtotime($yearData['clearances'][0]['date_submitted'])); ?></div>
                        </div>
                    </div>
                    <div class="signature-row">
                        <div class="signature-box">
                            <div class="signature-label">Teacher Signature</div>
                            <div class="signature-line"></div>
                            <div class="name-label">Over Printed Name</div>
                        </div>
                        <div class="signature-box">
                            <div class="signature-label">Department Head</div>
                            <div class="signature-line"></div>
                            <div class="name-label">Over Printed Name</div>
                        </div>
                    </div>
                    <div class="signature-row">
                        <div class="signature-box">
                            <div class="signature-label">Admin Signature</div>
                            <div class="signature-line"></div>
                            <div class="name-label">Over Printed Name</div>
                        </div>
                        <div class="signature-box">
                            <div class="signature-label">Date Cleared</div>
                            <div class="signature-line"></div>
                            <div class="name-label">
                                <?php 
                                $clearedDates = array_filter($yearData['clearances'], function($c) { return $c['date_cleared']; });
                                if (!empty($clearedDates)) {
                                    echo date('F j, Y', strtotime(max(array_column($clearedDates, 'date_cleared'))));
                                } else {
                                    echo 'Pending';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="footer">
                    <div class="footer-note">This form shows the status of your clearance requests.</div>
                    <div class="footer-note">Teachers will review and provide feedback on your clearance status.</div>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print Clearance Form
                </button>
            </div>
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
    border: 1px solid #333;
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
}

.status-rejected {
    background: #dc3545;
    color: white;
}

.bond-paper .remarks-section {
    margin-bottom: 25px;
}

.bond-paper .remarks-box {
    border: 1px solid #333;
    padding: 15px;
    min-height: 80px;
}

.bond-paper .remarks-content {
    font-size: 12px;
    min-height: 50px;
    border-bottom: 1px solid #333;
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
