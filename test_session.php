<?php
// Simple session test
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

echo "<h2>Session Test</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session data: " . print_r($_SESSION, true) . "</p>";

if (isset($_GET['login'])) {
    $_SESSION['test'] = 'Hello from session!';
    $_SESSION['user_id'] = 0;
    $_SESSION['username'] = 'admin';
    $_SESSION['role'] = 'admin';
    echo "<p style='color: green;'>Session data set!</p>";
    echo "<p><a href='test_session.php'>Click here to see if session persists</a></p>";
} else {
    if (isset($_SESSION['test'])) {
        echo "<p style='color: green;'>✓ Session working: " . $_SESSION['test'] . "</p>";
        if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
            echo "<p style='color: green;'>✓ Admin session data found!</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ No session data found</p>";
    }
    echo "<p><a href='test_session.php?login=1'>Click here to set session data</a></p>";
}
?>
