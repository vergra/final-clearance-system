<?php
$pageTitle = 'Request Clearance';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');
$pdo = getDB();
$user = getCurrentUser();
$lrn = $user['reference_id'];

// Get departments
$departments = $pdo->query("SELECT department_id, department_name FROM departments ORDER BY department_name")->fetchAll();

// Get current school year
$schoolYears = $pdo->query("SELECT school_year_id, year_label FROM school_year ORDER BY year_label DESC")->fetchAll();
$currentSchoolYear = $schoolYears[0] ?? null;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_id = (int)($_POST['department_id'] ?? 0);
    $strand_id = (int)($_POST['strand_id'] ?? 0);
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $teacher_id = (int)($_POST['teacher_id'] ?? 0);
    $school_year_id = (int)($_POST['school_year_id'] ?? 0);
    $requests = $_POST['requests'] ?? [];
    
    if (empty($requests)) {
        $error = 'Please select at least one clearance request.';
    } else {
        try {
            $pdo->beginTransaction();
            
            foreach ($requests as $requirement_id) {
                // Check if already exists
                $stmt = $pdo->prepare("SELECT clearance_id FROM clearance_status WHERE lrn = ? AND requirement_id = ? AND school_year_id = ?");
                $stmt->execute([$lrn, $requirement_id, $school_year_id]);
                
                if (!$stmt->fetch()) {
                    // Insert new clearance request
                    $stmt = $pdo->prepare("
                        INSERT INTO clearance_status (lrn, requirement_id, teacher_id, school_year_id, status, date_submitted)
                        VALUES (?, ?, ?, ?, 'Pending', CURDATE())
                    ");
                    $stmt->execute([$lrn, $requirement_id, $teacher_id, $school_year_id]);
                }
            }
            
            $pdo->commit();
            $success = 'Clearance requests submitted successfully!';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Failed to submit requests: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.bond-paper {
    background: white;
    border: 2px solid #333;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    padding: 30px;
    margin: 20px auto;
    max-width: 800px;
    font-family: 'Times New Roman', serif;
    line-height: 1.4;
}

.bond-paper .header {
    text-align: center;
    border-bottom: 2px solid #333;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.bond-paper .title {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 5px;
    text-transform: uppercase;
}

.bond-paper .subtitle {
    font-size: 14px;
    margin-bottom: 3px;
}

.bond-paper .student-details {
    margin: 20px 0;
    padding: 15px;
    border: 1px solid #333;
}

.bond-paper .details-row {
    display: flex;
    margin-bottom: 10px;
}

.bond-paper .details-row:last-child {
    margin-bottom: 0;
}

.bond-paper .detail-item {
    flex: 1;
    display: flex;
    align-items: center;
    font-size: 12px;
}

.bond-paper .detail-item:first-child {
    margin-right: 20px;
}

.bond-paper .detail-item label {
    font-weight: bold;
    width: 80px;
    margin-right: 10px;
}

.bond-paper .detail-item span {
    flex: 1;
    border-bottom: 1px solid #333;
    padding: 2px 5px;
    min-height: 18px;
}

.bond-paper .section-title {
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 15px;
    text-align: center;
    text-transform: uppercase;
    border-top: 1px solid #333;
    border-bottom: 1px solid #333;
    padding: 8px;
    background: #f9f9f9;
}

.bond-paper .clearance-section {
    margin-bottom: 25px;
}

.bond-paper .requirements-table {
    border: 1px solid #333;
}

.bond-paper .requirement-item {
    display: flex;
    align-items: center;
    padding: 8px 15px;
    border-bottom: 1px solid #ddd;
    font-size: 12px;
    min-height: 30px;
}

.bond-paper .requirement-item:last-child {
    border-bottom: none;
}

.bond-paper .requirement-checkbox {
    margin-right: 15px;
    transform: scale(1.1);
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
    margin: 10px 0 5px 0;
    height: 25px;
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
}

.bond-paper .footer-note {
    font-size: 11px;
    color: #666;
    margin-bottom: 5px;
}

.clearance-form {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.select-wrapper {
    position: relative;
}

.select-wrapper select {
    appearance: none;
    padding-right: 30px;
}

.select-wrapper::after {
    content: '▼';
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
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
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Request Clearance</h1>
    <a href="my_clearance.php" class="btn btn-outline-secondary">← Back to My Clearance</a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="clearance-form">
    <form method="post" action="" id="clearanceForm">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="department_id" class="form-label">Department</label>
                    <div class="select-wrapper">
                        <select class="form-select" id="department_id" name="department_id" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['department_id']; ?>"><?php echo htmlspecialchars($dept['department_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="school_year_id" class="form-label">School Year</label>
                    <div class="select-wrapper">
                        <select class="form-select" id="school_year_id" name="school_year_id" required>
                            <?php foreach ($schoolYears as $sy): ?>
                                <option value="<?php echo $sy['school_year_id']; ?>" <?php echo ($currentSchoolYear && $sy['school_year_id'] == $currentSchoolYear['school_year_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sy['year_label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="strand_id" class="form-label">Strand</label>
                    <div class="select-wrapper">
                        <select class="form-select" id="strand_id" name="strand_id" required disabled>
                            <option value="">Select Strand</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="subject_id" class="form-label">Subject</label>
                    <div class="select-wrapper">
                        <select class="form-select" id="subject_id" name="subject_id" required disabled>
                            <option value="">Select Subject</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="teacher_id" class="form-label">Teacher</label>
                    <div class="select-wrapper">
                        <select class="form-select" id="teacher_id" name="teacher_id" required disabled>
                            <option value="">Select Teacher</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label>&nbsp;</label>
                    <div>
                        <button type="button" class="btn btn-primary" id="generateForm">Generate Clearance Form</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="bondPaperContainer" style="display: none;">
    <div class="bond-paper">
        <div class="header">
            <div class="title">Student Clearance Request Form</div>
            <div class="subtitle">Gradline Senior High School</div>
            <div class="subtitle">School Year: <span id="displaySchoolYear"></span></div>
        </div>
        
        <div class="student-details">
            <div class="details-row">
                <div class="detail-item">
                    <label>Name:</label>
                    <span id="studentName"></span>
                </div>
                <div class="detail-item">
                    <label>Grade & Section:</label>
                    <span id="displayDepartment"></span>
                </div>
            </div>
            <div class="details-row">
                <div class="detail-item">
                    <label>LRN:</label>
                    <span><?php echo htmlspecialchars($lrn); ?></span>
                </div>
                <div class="detail-item">
                    <label>Strand:</label>
                    <span id="displayStrand"></span>
                </div>
            </div>
            <div class="details-row">
                <div class="detail-item">
                    <label>Subject:</label>
                    <span id="displaySubject"></span>
                </div>
                <div class="detail-item">
                    <label>Teacher:</label>
                    <span id="displayTeacher"></span>
                </div>
            </div>
        </div>
        
        <div class="clearance-section">
            <div class="requirements-table" id="requirementsList"></div>
        </div>
        
        <div class="remarks-section">
            <div class="section-title">REMARKS</div>
            <div class="remarks-box">
                <div class="remarks-content"></div>
            </div>
        </div>
        
        <div class="signature-section">
            <div class="signature-row">
                <div class="signature-box">
                    <div class="signature-label">Student Signature</div>
                    <div class="signature-line"></div>
                    <div class="name-label">Over Printed Name</div>
                </div>
                <div class="signature-box">
                    <div class="signature-label">Teacher Signature</div>
                    <div class="signature-line"></div>
                    <div class="name-label">Over Printed Name</div>
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
            <div class="footer-note">This form must be completed and submitted to the assigned teacher.</div>
            <div class="footer-note">The teacher will review and provide feedback on your clearance status.</div>
        </div>
    </div>
    
    <div class="text-center mt-3">
        <button type="button" class="btn btn-success btn-lg" id="submitRequests">Submit Clearance Request</button>
        <button type="button" class="btn btn-secondary btn-lg ms-2" id="printForm">Print Form</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('JavaScript loaded successfully');
    
    const departmentSelect = document.getElementById('department_id');
    const strandSelect = document.getElementById('strand_id');
    const subjectSelect = document.getElementById('subject_id');
    const teacherSelect = document.getElementById('teacher_id');
    const generateBtn = document.getElementById('generateForm');
    const bondPaperContainer = document.getElementById('bondPaperContainer');
    
    console.log('Elements found:', {
        departmentSelect: !!departmentSelect,
        strandSelect: !!strandSelect,
        subjectSelect: !!subjectSelect,
        teacherSelect: !!teacherSelect,
        generateBtn: !!generateBtn,
        bondPaperContainer: !!bondPaperContainer
    });
    
    let requirements = [];
    
    departmentSelect.addEventListener('change', function() {
        const deptId = this.value;
        strandSelect.innerHTML = '<option value="">Select Strand</option>';
        strandSelect.disabled = !deptId;
        subjectSelect.disabled = true;
        teacherSelect.disabled = true;
        
        if (deptId) {
            fetch(`get_strands.php?department_id=${deptId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(strand => {
                        const option = document.createElement('option');
                        option.value = strand.strand_id;
                        option.textContent = strand.strand_name;
                        strandSelect.appendChild(option);
                    });
                });
        }
    });
    
    strandSelect.addEventListener('change', function() {
        const strandId = this.value;
        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
        subjectSelect.disabled = !strandId;
        teacherSelect.disabled = true;
        
        if (strandId) {
            fetch(`get_subjects.php?strand_id=${strandId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(subject => {
                        const option = document.createElement('option');
                        option.value = subject.subject_id;
                        option.textContent = subject.subject_name;
                        subjectSelect.appendChild(option);
                    });
                });
        }
    });
    
    subjectSelect.addEventListener('change', function() {
        const subjectId = this.value;
        teacherSelect.innerHTML = '<option value="">Select Teacher</option>';
        teacherSelect.disabled = !subjectId;
        
        if (subjectId) {
            fetch(`get_teachers.php?subject_id=${subjectId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(teacher => {
                        const option = document.createElement('option');
                        option.value = teacher.teacher_id;
                        option.textContent = `${teacher.surname}, ${teacher.given_name}`;
                        teacherSelect.appendChild(option);
                    });
                });
        }
    });
    
    generateBtn.addEventListener('click', function() {
        console.log('Generate button clicked!');
        
        console.log('Department:', departmentSelect.value);
        console.log('Strand:', strandSelect.value);
        console.log('Subject:', subjectSelect.value);
        console.log('Teacher:', teacherSelect.value);
        
        if (!departmentSelect.value || !strandSelect.value || !subjectSelect.value || !teacherSelect.value) {
            alert('Please fill in all fields');
            return;
        }
        
        // Direct clearance request - no requirements needed
        displayBondPaper();
    });
    
    function displayBondPaper() {
        console.log('Displaying bond paper...');
        const deptText = departmentSelect.options[departmentSelect.selectedIndex].text;
        const strandText = strandSelect.options[strandSelect.selectedIndex].text;
        const subjectText = subjectSelect.options[subjectSelect.selectedIndex].text;
        const teacherText = teacherSelect.options[teacherSelect.selectedIndex].text;
        const schoolYearText = document.getElementById('school_year_id').options[document.getElementById('school_year_id').selectedIndex].text;
        
        console.log('Setting form values...');
        console.log('Elements found:', {
            displaySchoolYear: !!document.getElementById('displaySchoolYear'),
            studentName: !!document.getElementById('studentName'),
            displayDepartment: !!document.getElementById('displayDepartment'),
            displayStrand: !!document.getElementById('displayStrand'),
            displaySubject: !!document.getElementById('displaySubject'),
            displayTeacher: !!document.getElementById('displayTeacher')
        });
        
        const schoolYearElement = document.getElementById('displaySchoolYear');
        if (schoolYearElement) schoolYearElement.textContent = schoolYearText;
        
        const studentNameElement = document.getElementById('studentName');
        if (studentNameElement) studentNameElement.textContent = '<?php echo htmlspecialchars($user['username']); ?>';
        
        const displayDepartmentElement = document.getElementById('displayDepartment');
        if (displayDepartmentElement) displayDepartmentElement.textContent = deptText;
        
        const displayStrandElement = document.getElementById('displayStrand');
        if (displayStrandElement) displayStrandElement.textContent = strandText;
        
        const displaySubjectElement = document.getElementById('displaySubject');
        if (displaySubjectElement) displaySubjectElement.textContent = subjectText;
        
        const displayTeacherElement = document.getElementById('displayTeacher');
        if (displayTeacherElement) displayTeacherElement.textContent = teacherText;
        
        // Clear requirements section and show simple request message
        const requirementsList = document.getElementById('requirementsList');
        if (requirementsList) {
            requirementsList.innerHTML = `
                <div class="requirement-item">
                    <div style="flex: 1; text-align: center; padding: 20px;">
                        <h4>Clearance Request for ${subjectText}</h4>
                        <p>Student is requesting clearance from ${teacherText}</p>
                        <p style="font-size: 12px; color: #666; margin-top: 10px;">
                            The teacher will review this request and provide feedback on the student's completion status.
                        </p>
                    </div>
                </div>
            `;
        }
        
        console.log('Showing bond paper container...');
        bondPaperContainer.style.display = 'block';
        bondPaperContainer.scrollIntoView({ behavior: 'smooth' });
    }
    
    document.getElementById('submitRequests').addEventListener('click', function() {
        if (!confirm('Submit clearance request to ' + teacherSelect.options[teacherSelect.selectedIndex].text + '?')) {
            return;
        }
        
        // Create a simple clearance request
        const formData = new FormData();
        formData.append('department_id', departmentSelect.value);
        formData.append('strand_id', strandSelect.value);
        formData.append('subject_id', subjectSelect.value);
        formData.append('teacher_id', teacherSelect.value);
        formData.append('school_year_id', document.getElementById('school_year_id').value);
        
        fetch('submit_clearance_request.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Clearance request submitted successfully!');
                window.location.href = 'my_clearance.php';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error submitting request. Please try again.');
        });
    });
    
    document.getElementById('printForm').addEventListener('click', function() {
        window.print();
    });
});
</script>

<style>
@media print {
    .clearance-form, .btn, .d-flex, header, footer {
        display: none !important;
    }
    .bond-paper {
        box-shadow: none;
        border: 1px solid #333;
        margin: 0;
        padding: 20px;
        page-break-after: always;
    }
    .bond-paper .student-info {
        page-break-inside: avoid;
    }
    .bond-paper .requirements-grid {
        page-break-inside: avoid;
    }
    .bond-paper .signature-section {
        page-break-inside: avoid;
    }
    body {
        margin: 0;
        padding: 0;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
