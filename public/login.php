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

$pageTitle = 'Login';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (getCurrentUser() !== null) {
    header('Location: ' . rtrim(WEB_BASE, '/') . '/public/index.php');
    exit;
}

$roleLabels = ['student' => 'Student', 'teacher' => 'Teacher', 'admin' => 'Admin'];
$error = '';
$as = isset($_GET['as']) && in_array($_GET['as'], ['student', 'teacher', 'admin'], true) ? $_GET['as'] : (isset($_POST['as']) && in_array($_POST['as'], ['student', 'teacher', 'admin'], true) ? $_POST['as'] : null);
if ($as === null) {
    header('Location: ' . rtrim(WEB_BASE, '/') . '/public/index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $as = isset($_POST['as']) && in_array($_POST['as'], ['student', 'teacher', 'admin'], true) ? $_POST['as'] : $as;
    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        $loginResult = attemptLogin($username, $password, $as);
        if ($loginResult === true) {
            // If next=dashboard and role is teacher, go to teacher dashboard
            if (isset($_GET['next']) && $_GET['next'] === 'dashboard' && $as === 'teacher') {
                $redirect = rtrim(WEB_BASE, '/') . '/teacher/clearance/index.php';
            } else {
                $redirect = $_SESSION['login_redirect'] ?? rtrim(WEB_BASE, '/') . '/public/index.php';
            }
            unset($_SESSION['login_redirect']);
            header('Location: ' . $redirect);
            exit;
        }
        if ($loginResult === false) {
            $error = 'Invalid username or password.';
        } else {
            $actualLabel = $roleLabels[$loginResult];
            $error = 'This account is a ' . $actualLabel . ' account. Please go back to the home page and use "Log in as ' . $actualLabel . '" to sign in.';
        }
    }
}

$base = rtrim(WEB_BASE, '/');
$roleSubtext = [
    'student' => 'Sign in to view your clearance status',
    'teacher' => 'Sign in to review and approve clearances',
    'admin'   => 'Sign in to manage the system'
];
$roleIcon = ['student' => 'bi-person-video3 text-primary', 'teacher' => 'bi-person-badge text-success', 'admin' => 'bi-gear-wide-connected text-secondary'];
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
    <main class="container flex-grow-1 d-flex align-items-center justify-content-center py-5">
        <div class="card shadow-sm" style="width: 100%; max-width: 360px;">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi <?php echo $as ? $roleIcon[$as] : 'bi-check2-square text-primary'; ?>" style="font-size: 2.5rem;"></i>
                    <h1 class="h4 mt-2">Gradline Clearance</h1>
                    <p class="text-muted small"><?php echo $as ? $roleSubtext[$as] : 'Sign in to your account'; ?></p>
                    <?php if ($as): ?>
                        <span class="badge bg-secondary">Logging in as <?php echo htmlspecialchars($roleLabels[$as]); ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="post" action="" autocomplete="on">
                    <?php if ($as): ?><input type="hidden" name="as" value="<?php echo htmlspecialchars($as); ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label for="username" class="form-label"><?php echo ($as === 'student' || $as === 'teacher') ? 'University email' : 'Username'; ?></label>
                        <input type="<?php echo ($as === 'student' || $as === 'teacher') ? 'email' : 'text'; ?>" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($_GET['username'] ?? $_POST['username'] ?? ''); ?>" autocomplete="<?php echo ($as === 'student' || $as === 'teacher') ? 'email' : 'username'; ?>" autofocus required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Sign in</button>
                </form>
                <?php if ($as === 'student'): ?>
                <p class="text-center mt-3 mb-0 small">
                    Don't have an account? <a href="<?php echo $base; ?>/public/signup.php?as=student">Sign up</a>
                </p>
                <?php endif; ?>
                <?php if ($as === 'teacher'): ?>
                <p class="text-center mt-3 mb-0 small">
                    Don't have an account? <a href="<?php echo $base; ?>/public/signup_teacher.php?as=teacher">Sign up</a>
                </p>
                <?php endif; ?>
                <p class="text-center mt-2 mb-0 small text-muted">
                    <a href="<?php echo $base; ?>/">← Back to home</a>
                </p>
            </div>
        </div>
    </main>
</body>
</html>
