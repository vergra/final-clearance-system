<?php
$pageTitle = 'Teacher sign up';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (getCurrentUser() !== null) {
    header('Location: ' . rtrim(WEB_BASE, '/') . '/public/index.php');
    exit;
}

$as = isset($_GET['as']) && $_GET['as'] === 'teacher' ? 'teacher' : null;
if ($as === null) {
    header('Location: ' . rtrim(WEB_BASE, '/') . '/public/index.php');
    exit;
}

$pdo = getDB();

// Initialize arrays early to prevent undefined variable warnings
$departments = [];
$subjectsData = [];
$strandsData = [];
$dbError = '';

$error = '';
$success = false;
$surname = $middle_name = $given_name = $department = $strand = $email = '';
$department_id = 0;
$subject_id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $surname = trim($_POST['surname'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $given_name = trim($_POST['given_name'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);
    $strand = trim($_POST['strand'] ?? '');
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($surname === '' || $given_name === '' || !$department_id || $email === '' || $password === '') {
        $error = 'Please fill in all required fields (Surname, Given name, Department, University email, Password).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid university email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $password_confirm) {
        $error = 'Password and confirmation do not match.';
    } else {
        $exists = $pdo->prepare('SELECT user_id FROM users WHERE username = ?');
        $exists->execute([$email]);
        if ($exists->fetch()) {
            $error = 'This university email is already registered. Please log in or use a different email.';
        } else {
            try {
                $pdo->beginTransaction();
                $deptName = null;
                foreach ($departments as $d) {
                    if ((int)$d['department_id'] === $department_id) {
                        $deptName = $d['department_name'];
                        break;
                    }
                }
                
                // Try to insert with text fields first, fallback to basic insert
                try {
                    $ins = $pdo->prepare('INSERT INTO teachers (surname, middle_name, given_name, department_id, subject_id, department, strand) VALUES (?, ?, ?, ?, ?, ?, ?)');
                    $ins->execute([$surname, $middle_name !== '' ? $middle_name : null, $given_name, $department_id, $subject_id > 0 ? $subject_id : null, $deptName, $strand !== '' ? $strand : null]);
                } catch (PDOException $e) {
                    // If text columns don't exist, use basic insert
                    if (strpos($e->getMessage(), 'Unknown column') !== false) {
                        $ins = $pdo->prepare('INSERT INTO teachers (surname, middle_name, given_name, department_id, subject_id) VALUES (?, ?, ?, ?, ?)');
                        $ins->execute([$surname, $middle_name !== '' ? $middle_name : null, $given_name, $department_id, $subject_id > 0 ? $subject_id : null]);
                    } else {
                        throw $e;
                    }
                }
                
                $teacher_id = (int) $pdo->lastInsertId();
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $userIns = $pdo->prepare('INSERT INTO users (username, password_hash, role, reference_id) VALUES (?, ?, ?, ?)');
                $userIns->execute([$email, $hash, 'teacher', (string) $teacher_id]);
                $pdo->commit();
                $success = true;
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = 'Could not create account. Please try again. Error: ' . $e->getMessage();
            }
        }
    }
}

// Load departments and subjects data for the form
try {
    $departments = $pdo->query("SELECT department_id, department_name FROM departments ORDER BY department_name")->fetchAll();
    
    // Try to load subjects with strand column, fallback without it
    try {
        $subjectsData = $pdo->query("SELECT subject_id, subject_name, strand, department_id FROM subjects ORDER BY subject_name")->fetchAll();
        $strandsData = $pdo->query("SELECT DISTINCT strand as strand_name, department_id FROM subjects ORDER BY strand")->fetchAll();
    } catch (PDOException $e) {
        // If strand column doesn't exist, load subjects without it and get strands from strands table
        if (strpos($e->getMessage(), 'Unknown column') !== false) {
            $subjectsData = $pdo->query("SELECT subject_id, subject_name, strand_id, department_id FROM subjects ORDER BY subject_name")->fetchAll();
            $strandsData = $pdo->query("SELECT strand_id, strand_name, department_id FROM strands ORDER BY strand_name")->fetchAll();
        } else {
            throw $e;
        }
    }
} catch (PDOException $e) {
    $departments = [];
    $subjectsData = [];
    $strandsData = [];
    $dbError = 'Database connection error: ' . $e->getMessage();
    error_log("Database error in teacher signup: " . $e->getMessage());
}

$base = rtrim(WEB_BASE, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Gradline SHS Clearance</title>
    <link href="<?php echo $base; ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $base; ?>/assets/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $base; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <main class="container flex-grow-1 py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="bi bi-person-badge text-success" style="font-size: 2.5rem;"></i>
                            <h1 class="h4 mt-2">Teacher sign up</h1>
                            <p class="text-muted small">Enter your details and university email to create your account.</p>
                        </div>
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <strong>Account created.</strong> You can now log in with your university email and password.
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-center">
                                <a href="<?php echo $base; ?>/public/login.php?as=teacher&username=<?php echo urlencode($email); ?>&next=dashboard" class="btn btn-success">Proceed to Dashboard</a>
                                <a href="<?php echo $base; ?>/public/login.php?as=teacher&username=<?php echo urlencode($email); ?>" class="btn btn-outline-secondary">Back to Login</a>
                            </div>
                        <?php else: ?>
                            <?php if ($error): ?>
                                <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                            <?php if ($dbError): ?>
                                <div class="alert alert-danger py-2"><?php echo htmlspecialchars($dbError); ?></div>
                            <?php endif; ?>
                            <form method="post" action="" autocomplete="on">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label for="surname" class="form-label">Surname <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="surname" name="surname" value="<?php echo htmlspecialchars($surname); ?>" autocomplete="family-name" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="given_name" class="form-label">Given name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="given_name" name="given_name" value="<?php echo htmlspecialchars($given_name); ?>" autocomplete="given-name" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="middle_name" class="form-label">Middle name</label>
                                        <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($middle_name); ?>" autocomplete="additional-name">
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label for="department_id" class="form-label">Department assigned <span class="text-danger">*</span></label>
                                    <select class="form-select" id="department_id" name="department_id" autocomplete="organization" required>
                                        <option value="">— Select department —</option>
                                        <?php if (!empty($departments) && is_array($departments)): ?>
                                            <?php foreach ($departments as $d): ?>
                                                <option value="<?php echo (int)$d['department_id']; ?>" <?php echo $department_id === (int)$d['department_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['department_name']); ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <?php if (empty($departments)): ?>
                                        <p class="small text-muted mt-1 mb-0">No departments configured yet. Ask admin to add departments first.</p>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-2">
                                    <label for="strand" class="form-label">Strand <span class="text-danger">*</span></label>
                                    <select class="form-select" id="strand" name="strand" autocomplete="organization-title" required>
                                        <option value="">— Select department first —</option>
                                    </select>
                                </div>
                                <div class="mt-2">
                                    <label for="subject_id" class="form-label">Subject</label>
                                    <select class="form-select" id="subject_id" name="subject_id" autocomplete="job-title">
                                        <option value="">— Select strand first —</option>
                                    </select>
                                </div>
                                <hr class="my-3">
                                <div class="mb-2">
                                    <label for="email" class="form-label">University email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">@</span>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="name@university.edu" autocomplete="email" required>
                                    </div>
                                    <small class="text-muted">Must include @ symbol (e.g. name@university.edu)</small>
                                </div>
                                <div class="mb-2">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" minlength="6" autocomplete="new-password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirm" class="form-label">Confirm password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" minlength="6" autocomplete="new-password" required>
                                </div>
                                <button type="submit" class="btn btn-success w-100">Create account</button>
                            </form>
                        <?php endif; ?>
                        <p class="text-center mt-3 mb-0 small text-muted">
                            <a href="<?php echo $base; ?>/public/login.php?as=teacher">← Back to Teacher login</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
    (function() {
        // Handle both old and new database structures
        var subjects = <?php echo json_encode(array_map(function($r) { 
            return [
                'subject_id' => (int)$r['subject_id'], 
                'subject_name' => $r['subject_name'], 
                'strand' => $r['strand'] ?? null, // old structure
                'strand_id' => $r['strand_id'] ?? null, // new structure
                'department_id' => (int)$r['department_id']
            ]; 
        }, $subjectsData ?? [])); ?>;
        
        var strands = <?php echo json_encode(array_map(function($r) { 
            return [
                'strand_name' => $r['strand_name'] ?? null, // old structure
                'strand_id' => $r['strand_id'] ?? null, // new structure
                'department_id' => (int)$r['department_id']
            ]; 
        }, $strandsData ?? [])); ?>;
        
        var departmentSelect = document.getElementById('department_id');
        var strandSelect = document.getElementById('strand');
        var subjectSelect = document.getElementById('subject_id');

        // Safety check
        if (!departmentSelect || !strandSelect || !subjectSelect) {
            console.error('Required select elements not found');
            return;
        }

        function getStrandsForDepartment(departmentId) {
            var strandsList = [];
            var seen = {};
            if (strands && Array.isArray(strands)) {
                strands.forEach(function(s) {
                    var strandName = s.strand_name; // from strands table
                    if (strandName && s.department_id === departmentId && !seen[strandName]) {
                        seen[strandName] = true;
                        strandsList.push(strandName);
                    }
                });
            }
            // Also check subjects for backward compatibility
            if (subjects && Array.isArray(subjects)) {
                subjects.forEach(function(s) {
                    var strandName = s.strand; // from subjects table
                    if (strandName && s.department_id === departmentId && !seen[strandName]) {
                        seen[strandName] = true;
                        strandsList.push(strandName);
                    }
                });
            }
            return strandsList.sort();
        }

        function getSubjectsForDepartmentAndStrand(departmentId, strandName) {
            if (!subjects || !Array.isArray(subjects)) {
                return [];
            }
            return subjects.filter(function(s) {
                return s.department_id === departmentId && s.strand === strandName;
            });
        }

        function fillStrands() {
            var did = parseInt(departmentSelect.value, 10);
            strandSelect.innerHTML = '<option value="">— Select strand —</option>';
            subjectSelect.innerHTML = '<option value="">— Select strand first —</option>';
            if (!did) {
                strandSelect.innerHTML = '<option value="">— Select department first —</option>';
                return;
            }
            getStrandsForDepartment(did).forEach(function(s) {
                var opt = document.createElement('option');
                opt.value = s;
                opt.textContent = s;
                strandSelect.appendChild(opt);
            });
        }

        function fillSubjects() {
            var did = parseInt(departmentSelect.value, 10);
            var strandVal = strandSelect.value;
            subjectSelect.innerHTML = '<option value="">— Optional —</option>';
            if (!did || !strandVal) {
                subjectSelect.innerHTML = '<option value="">— Select strand first —</option>';
                return;
            }
            getSubjectsForDepartmentAndStrand(did, strandVal).forEach(function(s) {
                var opt = document.createElement('option');
                opt.value = s.subject_id;
                opt.textContent = s.subject_name;
                subjectSelect.appendChild(opt);
            });
        }

        departmentSelect.addEventListener('change', function() {
            fillStrands();
            fillSubjects();
        });
        strandSelect.addEventListener('change', fillSubjects);

        // Initial state: if page has selected values (e.g. validation error), restore them
        var initialStrand = <?php echo json_encode($strand ?? ''); ?>;
        var initialSubjectId = <?php echo json_encode((int)($subject_id ?? 0)); ?>;
        if (departmentSelect.value) {
            fillStrands();
            if (initialStrand) { strandSelect.value = initialStrand; }
            fillSubjects();
            if (initialSubjectId) { subjectSelect.value = String(initialSubjectId); }
        }
    })();
    </script>
</body>
</html>
