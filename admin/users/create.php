<?php
$baseUrl = '..';
$pageTitle = 'Add User Account';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$students = $pdo->query("SELECT lrn, surname, given_name FROM students ORDER BY surname, given_name")->fetchAll();
$teachers = $pdo->query("SELECT teacher_id, surname, given_name FROM teachers ORDER BY surname, given_name")->fetchAll();

$error = '';
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'student';
$reference_id = trim($_POST['reference_id'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($username === '') {
        $error = 'Username is required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($role === 'admin' && $reference_id !== '') {
        $error = 'Admin users do not have a reference.';
    } elseif (in_array($role, ['student', 'teacher'], true) && $reference_id === '') {
        $error = 'Please select a ' . $role . ' to link this account to.';
    } else {
        if ($role === 'student') {
            $valid = false;
            foreach ($students as $s) { if ((string)$s['lrn'] === $reference_id) { $valid = true; break; } }
            if (!$valid) $error = 'Invalid student LRN.';
        } elseif ($role === 'teacher') {
            $valid = false;
            foreach ($teachers as $t) { if ((string)$t['teacher_id'] === $reference_id) { $valid = true; break; } }
            if (!$valid) $error = 'Invalid teacher.';
        }
        if ($error === '') {
            $existing = $pdo->prepare('SELECT user_id FROM users WHERE username = ?');
            $existing->execute([$username]);
            if ($existing->fetch()) {
                $error = 'That username is already taken.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ref = ($role === 'admin') ? null : $reference_id;
                $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, role, reference_id) VALUES (?, ?, ?, ?)');
                $stmt->execute([$username, $hash, $role, $ref]);
                header('Location: index.php?created=1');
                exit;
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Add User Account</h1>
    <a href="index.php" class="btn btn-outline-secondary">Back to list</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" minlength="6" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role">
                    <option value="student" <?php echo $role === 'student' ? 'selected' : ''; ?>>Student</option>
                    <option value="teacher" <?php echo $role === 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                    <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <div class="mb-3" id="ref-wrap" style="display:<?php echo in_array($role, ['student','teacher'], true) ? 'block' : 'none'; ?>">
                <label for="reference_id" class="form-label" id="ref-label">Student (LRN)</label>
                <select class="form-select" id="reference_id" name="reference_id">
                    <?php if ($role === 'student'): ?>
                        <option value="">-- Select student --</option>
                        <?php foreach ($students as $s): ?>
                            <option value="<?php echo htmlspecialchars($s['lrn']); ?>" <?php echo $reference_id === (string)$s['lrn'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['surname'] . ', ' . $s['given_name'] . ' (' . $s['lrn'] . ')'); ?></option>
                        <?php endforeach; ?>
                    <?php elseif ($role === 'teacher'): ?>
                        <option value="">-- Select teacher --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?php echo (int)$t['teacher_id']; ?>" <?php echo $reference_id === (string)$t['teacher_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['surname'] . ', ' . $t['given_name']); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create user</button>
        </form>
    </div>
</div>

<script>
(function() {
    var roleSel = document.getElementById('role');
    var refWrap = document.getElementById('ref-wrap');
    var refLabel = document.getElementById('ref-label');
    var refSel = document.getElementById('reference_id');
    var studentOpts = <?php echo json_encode(array_map(function($s) { return ['value' => $s['lrn'], 'text' => $s['surname'] . ', ' . $s['given_name'] . ' (' . $s['lrn'] . ')']; }, $students)); ?>;
    var teacherOpts = <?php echo json_encode(array_map(function($t) { return ['value' => (string)$t['teacher_id'], 'text' => $t['surname'] . ', ' . $t['given_name']]; }, $teachers)); ?>;

    function setRefOptions(role) {
        refSel.innerHTML = '';
        if (role === 'student') {
            refLabel.textContent = 'Student (LRN)';
            refSel.appendChild(new Option('-- Select student --', ''));
            studentOpts.forEach(function(o) { refSel.appendChild(new Option(o.text, o.value)); });
        } else if (role === 'teacher') {
            refLabel.textContent = 'Teacher';
            refSel.appendChild(new Option('-- Select teacher --', ''));
            teacherOpts.forEach(function(o) { refSel.appendChild(new Option(o.text, o.value)); });
        }
    }

    roleSel.addEventListener('change', function() {
        var r = this.value;
        refWrap.style.display = (r === 'student' || r === 'teacher') ? 'block' : 'none';
        setRefOptions(r);
    });
})();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
