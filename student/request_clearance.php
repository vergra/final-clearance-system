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
    padding: 40px;
    margin: 20px auto;
    max-width: 800px;
    font-family: 'Times New Roman', serif;
}

.bond-paper .header {
    text-align: center;
    border-bottom: 2px solid #333;
    padding-bottom: 20px;
    margin-bottom: 30px;
}

.bond-paper .title {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 10px;
}

.bond-paper .subtitle {
    font-size: 16px;
    margin-bottom: 5px;
}

.bond-paper .form-section {
    margin-bottom: 25px;
}

.bond-paper .form-row {
    display: flex;
    margin-bottom: 15px;
    align-items: center;
}

.bond-paper .form-label {
    width: 150px;
    font-weight: bold;
    font-size: 14px;
}

.bond-paper .form-value {
    flex: 1;
    border-bottom: 1px solid #333;
    padding: 5px;
    min-height: 25px;
}

.bond-paper .requirements-section {
    margin-top: 30px;
}

.bond-paper .requirement-item {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    padding: 10px;
    border: 1px solid #ddd;
}

.bond-paper .requirement-checkbox {
    margin-right: 15px;
    transform: scale(1.2);
}

.bond-paper .footer {
    margin-top: 40px;
    text-align: center;
    border-top: 1px solid #333;
    padding-top: 20px;
    font-size: 12px;
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
            <div class="title">CLEARANCE REQUEST FORM</div>
            <div class="subtitle">Gradline Senior High School</div>
            <div class="subtitle">School Year: <span id="displaySchoolYear"></span></div>
        </div>
        
        <div class="form-section">
            <div class="form-row">
                <div class="form-label">Student Name:</div>
                <div class="form-value" id="studentName"></div>
            </div>
            <div class="form-row">
                <div class="form-label">LRN:</div>
                <div class="form-value"><?php echo htmlspecialchars($lrn); ?></div>
            </div>
            <div class="form-row">
                <div class="form-label">Department:</div>
                <div class="form-value" id="displayDepartment"></div>
            </div>
            <div class="form-row">
                <div class="form-label">Strand:</div>
                <div class="form-value" id="displayStrand"></div>
            </div>
            <div class="form-row">
                <div class="form-label">Subject:</div>
                <div class="form-value" id="displaySubject"></div>
            </div>
            <div class="form-row">
                <div class="form-label">Teacher:</div>
                <div class="form-value" id="displayTeacher"></div>
            </div>
        </div>
        
        <div class="requirements-section">
            <div style="font-weight: bold; margin-bottom: 15px;">CLEARANCE REQUIREMENTS:</div>
            <div id="requirementsList"></div>
        </div>
        
        <div class="footer">
            <div>Please check the requirements you need to clear.</div>
            <div style="margin-top: 10px;">Date: <?php echo date('F j, Y'); ?></div>
        </div>
    </div>
    
    <div class="text-center mt-3">
        <button type="button" class="btn btn-success btn-lg" id="submitRequests">Submit Selected Requests</button>
        <button type="button" class="btn btn-secondary btn-lg ms-2" id="printForm">Print Form</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const departmentSelect = document.getElementById('department_id');
    const strandSelect = document.getElementById('strand_id');
    const subjectSelect = document.getElementById('subject_id');
    const teacherSelect = document.getElementById('teacher_id');
    const generateBtn = document.getElementById('generateForm');
    const bondPaperContainer = document.getElementById('bondPaperContainer');
    
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
        if (!departmentSelect.value || !strandSelect.value || !subjectSelect.value || !teacherSelect.value) {
            alert('Please fill in all fields');
            return;
        }
        
        // Get requirements for the department
        fetch(`get_requirements.php?department_id=${departmentSelect.value}`)
            .then(response => response.json())
            .then(data => {
                requirements = data;
                displayBondPaper();
            });
    });
    
    function displayBondPaper() {
        const deptText = departmentSelect.options[departmentSelect.selectedIndex].text;
        const strandText = strandSelect.options[strandSelect.selectedIndex].text;
        const subjectText = subjectSelect.options[subjectSelect.selectedIndex].text;
        const teacherText = teacherSelect.options[teacherSelect.selectedIndex].text;
        const schoolYearText = document.getElementById('school_year_id').options[document.getElementById('school_year_id').selectedIndex].text;
        
        document.getElementById('displaySchoolYear').textContent = schoolYearText;
        document.getElementById('studentName').textContent = '<?php echo htmlspecialchars($user['username']); ?>';
        document.getElementById('displayDepartment').textContent = deptText;
        document.getElementById('displayStrand').textContent = strandText;
        document.getElementById('displaySubject').textContent = subjectText;
        document.getElementById('displayTeacher').textContent = teacherText;
        
        const requirementsList = document.getElementById('requirementsList');
        requirementsList.innerHTML = '';
        
        requirements.forEach(req => {
            const item = document.createElement('div');
            item.className = 'requirement-item';
            item.innerHTML = `
                <input type="checkbox" class="requirement-checkbox" name="requests[]" value="${req.requirement_id}">
                <span>${req.requirement_name}</span>
            `;
            requirementsList.appendChild(item);
        });
        
        bondPaperContainer.style.display = 'block';
        bondPaperContainer.scrollIntoView({ behavior: 'smooth' });
    }
    
    document.getElementById('submitRequests').addEventListener('click', function() {
        const selectedRequests = document.querySelectorAll('input[name="requests[]"]:checked');
        if (selectedRequests.length === 0) {
            alert('Please select at least one requirement to request');
            return;
        }
        
        document.getElementById('clearanceForm').submit();
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
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
