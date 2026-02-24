<?php
$pageTitle = 'Teacher Clearance Form';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(['admin', 'teacher']);
$pdo = getDB();
$user = getCurrentUser();
$teacher_id = $user['reference_id'];

// Get teacher info
$stmt = $pdo->prepare("SELECT t.*, d.department_name FROM teachers t JOIN departments d ON d.department_id = t.department_id WHERE t.teacher_id = ?");
$stmt->execute([$teacher_id]);
$teacher = $stmt->fetch();

// Get current school year
$schoolYears = $pdo->query("SELECT school_year_id, year_label FROM school_year ORDER BY year_label DESC")->fetchAll();
$currentSchoolYear = $schoolYears[0] ?? null;

// Get students assigned to this teacher
$stmt = $pdo->prepare("
    SELECT s.lrn, s.surname, s.given_name, s.middle_name, s.block_code,
           sub.subject_name, c.status, c.date_submitted, c.date_cleared, c.remarks
    FROM student_subject ss
    JOIN students s ON s.lrn = ss.lrn
    JOIN subjects sub ON sub.subject_id = ss.subject_id
    LEFT JOIN clearance_status c ON c.lrn = s.lrn AND c.subject_id = ss.subject_id AND c.teacher_id = ?
    WHERE ss.teacher_id = ? AND ss.school_year_id = ?
    ORDER BY s.surname, s.given_name, sub.subject_name
");
$stmt->execute([$teacher_id, $teacher_id, $currentSchoolYear['school_year_id']]);
$students = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Teacher Clearance Form</h1>
    <div>
        <a href="../public/index.php" class="btn btn-outline-secondary me-2">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer me-1"></i> Print Form
        </button>
    </div>
</div>

<div class="bond-paper">
    <div class="header">
        <div class="title">Teacher Clearance Form</div>
        <div class="subtitle">Gradline Senior High School</div>
        <div class="subtitle">School Year: <?php echo htmlspecialchars($currentSchoolYear['year_label']); ?></div>
    </div>
    
    <div class="teacher-details">
        <div class="details-row">
            <div class="detail-item">
                <label>Teacher Name:</label>
                <span><?php echo htmlspecialchars($teacher['surname'] . ', ' . $teacher['given_name'] . ' ' . $teacher['middle_name']); ?></span>
            </div>
            <div class="detail-item">
                <label>Department:</label>
                <span><?php echo htmlspecialchars($teacher['department_name']); ?></span>
            </div>
        </div>
    </div>
    
    <div class="students-section">
        <div class="section-title">STUDENTS CLEARANCE STATUS</div>
        <div class="students-list">
            <?php foreach ($students as $student): ?>
                <div class="student-item">
                    <div class="student-info">
                        <div class="student-name"><?php echo htmlspecialchars($student['surname'] . ', ' . $student['given_name']); ?></div>
                        <div class="student-details">
                            <span class="lrn">LRN: <?php echo htmlspecialchars($student['lrn']); ?></span>
                            <span class="block"><?php echo htmlspecialchars($student['block_code']); ?></span>
                        </div>
                        <div class="subject-name"><?php echo htmlspecialchars($student['subject_name']); ?></div>
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
                        $remarks[] = htmlspecialchars($student['subject_name'] . ' - ' . $student['surname'] . ': ' . $student['remarks']);
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
                <div class="name-label"><?php echo htmlspecialchars($teacher['surname'] . ', ' . $teacher['given_name']); ?></div>
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
        <div class="footer-note">This form is required for all teachers to complete clearance at the end of each school year.</div>
        <div class="footer-note">Please ensure all student clearances are properly reviewed and signed.</div>
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

.bond-paper .teacher-details {
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
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
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

.bond-paper .subject-name {
    font-size: 0.85rem;
    color: #555;
    margin-top: 4px;
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
