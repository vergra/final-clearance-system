<?php
$pageTitle = 'Sign up';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (getCurrentUser() !== null) {
    header('Location: ' . rtrim(WEB_BASE, '/') . '/public/index.php');
    exit;
}

$as = isset($_GET['as']) && $_GET['as'] === 'student' ? 'student' : null;
if ($as === null) {
    header('Location: ' . rtrim(WEB_BASE, '/') . '/public/index.php');
    exit;
}

$pdo = getDB();
// Load blocks and subjects data (for department → strand dropdowns)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
$dbError = '';
try {
    $blocks = $pdo->query("SELECT block_code, block_name FROM blocks ORDER BY block_code")->fetchAll();
    $departments = $pdo->query("SELECT department_id, department_name FROM departments ORDER BY department_name")->fetchAll(PDO::FETCH_ASSOC);
    $subjectsData = $pdo->query("SELECT strand_id, strand_name, department_id FROM strands ORDER BY department_id, strand_name")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $blocks = [];
    $departments = [];
    $subjectsData = [];
    $dbError = 'Database connection error: ' . $e->getMessage();
    error_log("Database error in student signup: " . $e->getMessage());
}

$error = '';
$success = false;
$lrn = $surname = $middle_name = $given_name = $department_id = $strand = $block_code = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lrn = trim($_POST['lrn'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $given_name = trim($_POST['given_name'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);
    $strand = trim($_POST['strand'] ?? '');
    $block_code = trim($_POST['block_code'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($lrn === '' || $surname === '' || $middle_name === '' || $given_name === '' || !$department_id || $strand === '' || $block_code === '' || $email === '' || $password === '') {
        $error = 'Please fill in all required fields (LRN, Surname, Middle name, Given name, Department, Strand, Block, University email, Password).';
    } elseif (empty($blocks)) {
        $error = 'No blocks are defined yet. Please ask your administrator to add blocks first.';
    } else {
        $validBlock = false;
        foreach ($blocks as $b) {
            if ((string)$b['block_code'] === (string)$block_code) {
                $validBlock = true;
                break;
            }
        }
        if (!$validBlock) {
            $error = 'Please select a valid block from the list.';
        }
    }
    if ($error === '' && !isset($validBlock)) {
        $validBlock = true; // skip block check when we didn't run it (e.g. empty required fields)
    }
    if ($error === '' && ($validBlock ?? true) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid university email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $password_confirm) {
        $error = 'Password and confirmation do not match.';
    } else {
        // Convert selected strand_id to strand_name for storing in students.strand (legacy schema)
        $selectedStrandId = (int)$strand;
        $selectedStrandName = '';
        foreach ($subjectsData as $sr) {
            if ((int)$sr['strand_id'] === $selectedStrandId) {
                $selectedStrandName = (string)$sr['strand_name'];
                break;
            }
        }
        if ($selectedStrandName === '') {
            $error = 'Please select a valid strand from the list.';
        }
    }

    if ($error === '') {
        $hasUser = $pdo->prepare('SELECT user_id FROM users WHERE role = ? AND reference_id = ?');
        $hasUser->execute(['student', $lrn]);
        if ($hasUser->fetch()) {
            $error = 'This LRN already has an account. Please log in.';
        } else {
            $exists = $pdo->prepare('SELECT user_id FROM users WHERE username = ?');
            $exists->execute([$email]);
            if ($exists->fetch()) {
                $error = 'This university email is already registered. Please log in or use a different email.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->beginTransaction();
                try {
                    // Upsert into students table so clearance form can display Strand/Grade/Block
                    $st = $pdo->prepare('SELECT lrn FROM students WHERE lrn = ?');
                    $st->execute([$lrn]);
                    if ($st->fetchColumn()) {
                        $up = $pdo->prepare('UPDATE students SET surname = ?, middle_name = ?, given_name = ?, strand = ?, block_code = ? WHERE lrn = ?');
                        $up->execute([$surname, $middle_name, $given_name, $selectedStrandName, $block_code, $lrn]);
                    } else {
                        $in = $pdo->prepare('INSERT INTO students (lrn, surname, middle_name, given_name, strand, block_code) VALUES (?, ?, ?, ?, ?, ?)');
                        $in->execute([$lrn, $surname, $middle_name, $given_name, $selectedStrandName, $block_code]);
                    }

                    $ins = $pdo->prepare('INSERT INTO users (username, password_hash, role, reference_id) VALUES (?, ?, ?, ?)');
                    $ins->execute([$email, $hash, 'student', $lrn]);
                    $pdo->commit();
                    $success = true;
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $error = 'Could not create account. Please try again. Error: ' . $e->getMessage();
                }
            }
        }
    }
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
                            <i class="bi bi-person-plus-fill text-primary" style="font-size: 2.5rem;"></i>
                            <h1 class="h4 mt-2">Student sign up</h1>
                            <p class="text-muted small">Enter your student details and university email. Fill in the fields correctly to create your account.</p>
                        </div>
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <strong>Account created.</strong> You can now log in with your university email and password.
                            </div>
                            <p class="text-center mb-0">
                                <a href="<?php echo $base; ?>/public/login.php?as=student&username=<?php echo urlencode($email); ?>" class="btn btn-primary">Proceed to Log in</a>
                            </p>
                        <?php else: ?>
                            <?php if ($error): ?>
                                <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                            <?php if ($dbError): ?>
                                <div class="alert alert-danger py-2"><?php echo htmlspecialchars($dbError); ?></div>
                            <?php endif; ?>
                            <form method="post" action="" autocomplete="on">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label for="lrn" class="form-label">LRN <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="lrn" name="lrn" value="<?php echo htmlspecialchars($lrn); ?>" autocomplete="off" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="block_code" class="form-label">Block <span class="text-danger">*</span></label>
                                        <select class="form-select" id="block_code" name="block_code" autocomplete="off" required <?php echo empty($blocks) ? 'disabled' : ''; ?>>
                                            <option value="">-- Select block --</option>
                                            <?php foreach ($blocks as $b): ?>
                                                <option value="<?php echo htmlspecialchars($b['block_code']); ?>" <?php echo $block_code === $b['block_code'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($b['block_code'] . ' – ' . $b['block_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (empty($blocks)): ?>
                                            <p class="small text-muted mt-1 mb-0">No blocks defined. Ask your administrator to add blocks in the admin dashboard.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
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
                                        <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($middle_name); ?>" autocomplete="additional-name" required>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                                    <select class="form-select" id="department_id" name="department_id" autocomplete="organization" required>
                                        <option value="">— Select department —</option>
                                        <?php foreach ($departments as $d): ?>
                                            <option value="<?php echo (int)$d['department_id']; ?>" <?php echo $department_id === (int)$d['department_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['department_name']); ?></option>
                                        <?php endforeach; ?>
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
                                <button type="submit" class="btn btn-primary w-100">Create account</button>
                            </form>
                        <?php endif; ?>
                        <p class="text-center mt-3 mb-0 small text-muted">
                            <a href="<?php echo $base; ?>/public/login.php?as=student">← Back to Student login</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
    (function() {
        var strandRows = <?php echo json_encode(array_map(function($r) { return ['strand_id' => (int)$r['strand_id'], 'strand_name' => $r['strand_name'], 'department_id' => (int)$r['department_id']]; }, $subjectsData)); ?>;
        var departmentSelect = document.getElementById('department_id');
        var strandSelect = document.getElementById('strand');

        function getStrandsForDepartment(departmentId) {
            return strandRows
                .filter(function(r) { return r.department_id === departmentId; })
                .map(function(r) { return { strand_id: r.strand_id, strand_name: r.strand_name }; })
                .sort(function(a, b) { return String(a.strand_name).localeCompare(String(b.strand_name)); });
        }

        function fillStrands() {
            var did = parseInt(departmentSelect.value, 10);
            strandSelect.innerHTML = '<option value="">— Select department first —</option>';
            if (!did) return;
            strandSelect.innerHTML = '<option value="">— Select strand —</option>';
            getStrandsForDepartment(did).forEach(function(s) {
                var opt = document.createElement('option');
                opt.value = String(s.strand_id);
                opt.textContent = s.strand_name;
                strandSelect.appendChild(opt);
            });
        }

        departmentSelect.addEventListener('change', fillStrands);

        var initialStrand = <?php echo json_encode($strand ?? ''); ?>;
        if (departmentSelect.value) {
            fillStrands();
            if (initialStrand) { strandSelect.value = initialStrand; }
        }
    })();
    </script>
</body>
</html>
