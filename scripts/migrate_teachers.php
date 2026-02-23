<?php
/**
 * Web-accessible migration script to add department and strand columns to teachers table
 * Access this file via: http://localhost/student_clearance/migrate_teachers.php
 */
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

$errors = [];
$success = [];

// Check if columns already exist
try {
    $check = $pdo->query("SHOW COLUMNS FROM teachers LIKE 'department'");
    if ($check->rowCount() > 0) {
        $success[] = "Column 'department' already exists.";
    } else {
        $pdo->exec("ALTER TABLE teachers ADD COLUMN department VARCHAR(100) DEFAULT NULL AFTER department_id");
        $success[] = "✓ Added 'department' column";
    }
} catch (PDOException $e) {
    $errors[] = "Error adding 'department' column: " . $e->getMessage();
}

try {
    $check = $pdo->query("SHOW COLUMNS FROM teachers LIKE 'strand'");
    if ($check->rowCount() > 0) {
        $success[] = "Column 'strand' already exists.";
    } else {
        $pdo->exec("ALTER TABLE teachers ADD COLUMN strand VARCHAR(50) DEFAULT NULL AFTER subject_id");
        $success[] = "✓ Added 'strand' column";
    }
} catch (PDOException $e) {
    $errors[] = "Error adding 'strand' column: " . $e->getMessage();
}

try {
    $pdo->exec("ALTER TABLE teachers MODIFY COLUMN department_id INT DEFAULT NULL");
    $success[] = "✓ Made 'department_id' nullable";
} catch (PDOException $e) {
    $errors[] = "Error modifying 'department_id': " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration: Teachers Table</title>
    <link href="/student_clearance/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Database Migration: Teachers Table</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <h5>Success:</h5>
                                <ul class="mb-0">
                                    <?php foreach ($success as $msg): ?>
                                        <li><?php echo htmlspecialchars($msg); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <h5>Errors:</h5>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $msg): ?>
                                        <li><?php echo htmlspecialchars($msg); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (empty($errors)): ?>
                            <div class="alert alert-info">
                                <strong>Migration completed!</strong> The teachers table now has the 'department' and 'strand' columns.
                                You can now try creating a teacher account again.
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <a href="../public/signup_teacher.php?as=teacher" class="btn btn-primary">Go to Teacher Signup</a>
                            <a href="../public/index.php" class="btn btn-secondary">Go to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
