<?php
$pageTitle = 'Subject Clearance Form';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(['admin', 'teacher']);
$pdo = getDB();
$user = getCurrentUser();

// Get parameters
$subject_id = (int)($_GET['subject_id'] ?? 0);
$school_year_id = (int)($_GET['school_year_id'] ?? 0);

if (!$subject_id || !$school_year_id) {
    // Get current school year if not specified
    $schoolYears = $pdo->query("SELECT school_year_id, year_label FROM school_year ORDER BY year_label DESC")->fetchAll();
    $currentSchoolYear = $schoolYears[0] ?? null;
    $school_year_id = $currentSchoolYear['school_year_id'];
}

// Get subject info
$stmt = $pdo->prepare("
    SELECT sub.*, d.department_name, st.strand_name, t.surname AS t_surname, t.given_name AS t_given
    FROM subjects sub
    JOIN departments d ON d.department_id = sub.department_id
    LEFT JOIN strands st ON st.strand_id = sub.strand_id
    LEFT JOIN teachers t ON t.teacher_id = sub.subject_id
    WHERE sub.subject_id = ?
");
$stmt->execute([$subject_id]);
$subject = $stmt->fetch();

// Get students enrolled in this subject
$stmt = $pdo->prepare("
    SELECT s.lrn, s.surname, s.given_name, s.middle_name, s.block_code,
           c.status, c.date_submitted, c.date_cleared, c.remarks
    FROM student_subject ss
    JOIN students s ON s.lrn = ss.lrn
    LEFT JOIN clearance_status c ON c.lrn = s.lrn AND c.subject_id = ss.subject_id
    WHERE ss.subject_id = ? AND ss.school_year_id = ?
    ORDER BY s.surname, s.given_name
");
$stmt->execute([$subject_id, $school_year_id]);
$students = $stmt->fetchAll();

// Get school year info
$stmt = $pdo->prepare("SELECT year_label FROM school_year WHERE school_year_id = ?");
$stmt->execute([$school_year_id]);
$schoolYear = $stmt->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Subject Clearance Form</h1>
    <div>
        <a href="../admin/subjects/index.php" class="btn btn-outline-secondary me-2">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer me-1"></i> Print Form
        </button>
    </div>
</div>

<div class="bond-paper">
    <div class="header">
        <div class="title">Subject Clearance Form</div>
        <div class="subtitle">Gradline Senior High School</div>
        <div class="subtitle">School Year: <?php echo htmlspecialchars($schoolYear['year_label']); ?></div>
    </div>
    
    <div class="subject-details">
        <div class="details-row">
            <div class="detail-item">
                <label>Subject Name:</label>
                <span><?php echo htmlspecialchars($subject['subject_name']); ?></span>
            </div>
            <div class="detail-item">
                <label>Strand:</label>
                <span><?php echo htmlspecialchars($subject['strand_name']); ?></span>
            </div>
        </div>
        <div class="details-row">
            <div class="detail-item">
                <label>Department:</label>
                <span><?php echo htmlspecialchars($subject['department_name']); ?></span>
            </div>
            <div class="detail-item">
                <label>Teacher:</label>
                <span><?php echo htmlspecialchars($subject['t_surname'] . ', ' . $subject['t_given']); ?></span>
            </div>
        </div>
    </div>
    
    <div class="students-section">
        <div class="section-title">STUDENT CLEARANCE STATUS</div>
        <div class="students-list">
            <?php foreach ($students as $student): ?>
                <div class="student-item">
                    <div class="student-info">
                        <div class="student-name"><?php echo htmlspecialchars($student['surname'] . ', ' . $student['given_name']); ?></div>
                        <div class="student-details">
                            <span class="lrn">LRN: <?php echo htmlspecialchars($student['lrn']); ?></span>
                            <span class="block"><?php echo htmlspecialchars($student['block_code']); ?></span>
                        </div>
                        <span class="status-badge status-<?php echo strtolower($student['status'] ?: 'pending'); ?>">
                            <?php echo htmlspecialchars($student['status'] ?: 'Pending'); ?>
                        </span>
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
                foreach ($students as $student) {
                    if (!empty($student['remarks'])) {
                        $remarks[] = htmlspecialchars($student['surname'] . ', ' . $student['given_name'] . ': ' . $student['remarks']);
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
                <div class="signature-label">Teacher Signature</div>
                <div class="signature-line"></div>
                <div class="name-label"><?php echo htmlspecialchars($subject['t_surname'] . ', ' . $subject['t_given']); ?></div>
            </div>
            <div class="signature-box">
                <div class="signature-label">Date Signed</div>
                <div class="signature-line"></div>
                <div class="name-label"><?php echo date('F j, Y'); ?></div>
            </div>
        </div>
        <div class="signature-row">
            <div class="signature-box">
                <div class="signature-label">Department Head</div>
                <div class="signature-line"></div>
                <div class="name-label">Over Printed Name</div>
            </div>
            <div class="signature-box">
                <div class="signature-label">Admin Signature</div>
                <div class="signature-line"></div>
                <div class="name-label">Over Printed Name</div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <div class="footer-note">This form certifies that all students enrolled in this subject have completed their clearance requirements.</div>
        <div class="footer-note">Please review all student records before signing.</div>
    </div>
</div>

<style>
.bond-paper {
    background: white;
    width: 8.5in;
    min-height: 11in;
    padding: 1in;
    margin: 20px auto;
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

.bond-paper .subject-details {
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

.bond-paper .students-section {
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

.bond-paper .students-list {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 15px;
}

.bond-paper .student-item {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 12px;
    font-size: 12px;
    min-height: 90px;
}

.bond-paper .student-info {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    flex: 1;
}

.bond-paper .student-name {
    font-weight: bold;
    font-size: 0.9rem;
    color: #333;
}

.bond-paper .student-details {
    display: flex;
    gap: 10px;
    font-size: 0.8rem;
    color: #666;
    margin: 4px 0;
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

    .bond-paper .students-list {
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
