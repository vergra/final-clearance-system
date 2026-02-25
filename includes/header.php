<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = isset($pageTitle) ? $pageTitle : 'Senior High School Clearance';
if (!defined('WEB_BASE')) {
    define('WEB_BASE', '/student_clearance');
}
$base = rtrim(WEB_BASE, '/');
require_once __DIR__ . '/auth.php';
$currentUser = getCurrentUser();
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
    <link href="<?php echo $base; ?>/assets/css/admin-cards.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo $base; ?>/public/index.php">
                <i class="bi bi-check2-square me-2"></i>Senior High School Clearance
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($currentUser): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>/public/index.php">Home</a></li>
                        <?php if ($currentUser['role'] === 'student'): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>/student/my_clearance.php">My Clearance</a></li>
                        <?php elseif ($currentUser['role'] === 'teacher'): ?>
                            <!-- Clearance functionality is now on homepage -->
                        <?php else: ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Master Data</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="<?php echo $base; ?>/admin/school_years/index.php">School Years</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $base; ?>/admin/departments/index.php">Departments</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $base; ?>/admin/blocks/index.php">Blocks</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $base; ?>/admin/teachers/index.php">Teachers</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $base; ?>/admin/students/index.php">Students</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo $base; ?>/admin/users/index.php">User accounts</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>/public/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout (<?php echo htmlspecialchars($currentUser['username']); ?>)</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container flex-grow-1 py-4">
