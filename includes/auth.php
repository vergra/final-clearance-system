<?php
/**
 * Authentication helpers for Gradline Clearance.
 * Users table: username, password_hash, role (student|teacher|admin), reference_id (LRN or teacher_id).
 */
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

require_once __DIR__ . '/../config/database.php';

/**
 * Get current logged-in user row or null.
 * @return array|null ['user_id','username','role','reference_id', ...] or null
 */
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    // Handle hardcoded admin account (user_id = 0)
    if ($_SESSION['user_id'] == 0 && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        return [
            'user_id' => 0,
            'username' => $_SESSION['username'] ?? 'admin',
            'role' => 'admin',
            'reference_id' => null,
            'created_at' => null
        ];
    }
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT user_id, username, role, reference_id, created_at FROM users WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}

/**
 * Require user to be logged in; redirect to login otherwise.
 * @param string $redirectUrl URL to return to after login (optional)
 */
function requireLogin($redirectUrl = null) {
    if (getCurrentUser() !== null) {
        return;
    }
    $_SESSION['login_redirect'] = $redirectUrl ?? ($_SERVER['REQUEST_URI'] ?? '');
    header('Location: ' . (defined('WEB_BASE') ? rtrim(WEB_BASE, '/') : '') . '/public/login.php');
    exit;
}

/**
 * Require user to have one of the given roles; redirect to login or home if not.
 * @param string|string[] $roles 'admin', 'teacher', 'student', or array of these
 */
function requireRole($roles) {
    requireLogin();
    $user = getCurrentUser();
    $allowed = is_array($roles) ? $roles : [$roles];
    if (!in_array($user['role'], $allowed, true)) {
        header('Location: ' . (defined('WEB_BASE') ? rtrim(WEB_BASE, '/') : '') . '/public/index.php');
        exit;
    }
}

/**
 * Attempt login. If $requiredRole is set, only that role can log in from this form.
 * @param string $username
 * @param string $password
 * @param string|null $requiredRole One of 'student', 'teacher', 'admin' to restrict by chosen login type
 * @return true on success (session set), false on invalid credentials, or string (actual role) when credentials are valid but account role does not match required (e.g. user chose "Student" but account is teacher)
 */
function attemptLogin($username, $password, $requiredRole = null) {
    // Hardcoded permanent admin account (always works regardless of database)
    $hardcodedAdminUsername = 'admin';
    $hardcodedAdminPassword = 'admin123'; // Change this to your desired permanent admin password
    
    // Check hardcoded admin credentials first
    if ($username === $hardcodedAdminUsername && $password === $hardcodedAdminPassword) {
        // If required role is set and it's not admin, return the role mismatch
        if ($requiredRole !== null && $requiredRole !== 'admin') {
            return 'admin';
        }
        // Set session for hardcoded admin (use special user_id 0 or negative to indicate hardcoded)
        $_SESSION['user_id'] = 0;
        $_SESSION['username'] = $hardcodedAdminUsername;
        $_SESSION['role'] = 'admin';
        $_SESSION['reference_id'] = null;
        return true;
    }
    
    // Check database for other users
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT user_id, username, password_hash, role, reference_id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($password, $row['password_hash'])) {
        return false;
    }
    if ($requiredRole !== null && $row['role'] !== $requiredRole) {
        return $row['role'];
    }
    $_SESSION['user_id'] = (int) $row['user_id'];
    $_SESSION['username'] = $row['username'];
    $_SESSION['role'] = $row['role'];
    $_SESSION['reference_id'] = $row['reference_id'];
    return true;
}

/**
 * Log out the current user.
 */
function logout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
