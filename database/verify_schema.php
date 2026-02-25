<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: text/html; charset=utf-8');

function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

try {
    $pdo = getDB();

    $requiredTables = [
        'departments',
        'strands',
        'school_year',
        'blocks',
        'users',
        'teachers',
        'students',
        'signup_requests',
        'subjects',
        'requirements',
        'student_subject',
        'teacher_subject',
        'clearance_status',
        'students_requirement',
        'students_clearance_status'
    ];

    $requiredColumns = [
        'users' => ['user_id', 'username', 'password_hash', 'role', 'reference_id'],
        'signup_requests' => ['signup_request_id', 'lrn', 'requested_username', 'password_hash', 'status'],
        'clearance_status' => ['clearance_id', 'lrn', 'requirement_id', 'teacher_id', 'subject_id', 'school_year_id', 'status', 'date_submitted', 'date_cleared', 'date_returned', 'remarks', 'request_group_id'],
        'student_subject' => ['student_subject_id', 'lrn', 'subject_id', 'school_year_id'],
        'teacher_subject' => ['teacher_subject_id', 'teacher_id', 'subject_id', 'school_year_id'],
    ];

    $requiredViews = [
        'teacher_pending_clearances',
        'student_clearance_history',
        'compliance_tracking'
    ];

    $requiredTriggers = [
        'before_clearance_insert'
    ];

    $requiredProcedures = [
        'GetStudentClearanceSummary',
        'GetTeacherWorkload'
    ];

    $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();

    echo '<!doctype html><html><head><meta charset="utf-8"><title>Schema Verification</title>';
    echo '<style>body{font-family:Arial, sans-serif; padding:20px;} .ok{color:#198754;} .bad{color:#dc3545;} table{border-collapse:collapse; width:100%; max-width:900px;} th,td{border:1px solid #ddd; padding:8px;} th{background:#f8f9fa;} .section{margin:20px 0;}</style>';
    echo '</head><body>';
    echo '<h2>Schema Verification</h2>';
    echo '<p><strong>Database:</strong> ' . h($dbName) . '</p>';

    $allOk = true;

    // Tables
    echo '<div class="section"><h3>Tables</h3><table><tr><th>Table</th><th>Status</th></tr>';
    foreach ($requiredTables as $t) {
        $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$t]);
        $exists = (bool)$stmt->fetchColumn();
        if (!$exists) {
            $allOk = false;
        }
        echo '<tr><td>' . h($t) . '</td><td class="' . ($exists ? 'ok' : 'bad') . '">' . ($exists ? 'OK' : 'MISSING') . '</td></tr>';
    }
    echo '</table></div>';

    // Columns
    echo '<div class="section"><h3>Columns</h3><table><tr><th>Table</th><th>Column</th><th>Status</th></tr>';
    foreach ($requiredColumns as $table => $cols) {
        foreach ($cols as $col) {
            $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
            try {
                $stmt->execute([$col]);
                $exists = (bool)$stmt->fetch();
            } catch (PDOException $e) {
                $exists = false;
            }
            if (!$exists) {
                $allOk = false;
            }
            echo '<tr><td>' . h($table) . '</td><td>' . h($col) . '</td><td class="' . ($exists ? 'ok' : 'bad') . '">' . ($exists ? 'OK' : 'MISSING') . '</td></tr>';
        }
    }
    echo '</table></div>';

    // Views
    echo '<div class="section"><h3>Views</h3><table><tr><th>View</th><th>Status</th></tr>';
    foreach ($requiredViews as $v) {
        $stmt = $pdo->prepare("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $stmt->execute([$v]);
        $exists = (bool)$stmt->fetchColumn();
        if (!$exists) {
            $allOk = false;
        }
        echo '<tr><td>' . h($v) . '</td><td class="' . ($exists ? 'ok' : 'bad') . '">' . ($exists ? 'OK' : 'MISSING') . '</td></tr>';
    }
    echo '</table></div>';

    // Triggers
    echo '<div class="section"><h3>Triggers</h3><table><tr><th>Trigger</th><th>Status</th></tr>';
    foreach ($requiredTriggers as $tr) {
        $stmt = $pdo->prepare("SELECT TRIGGER_NAME FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE() AND TRIGGER_NAME = ?");
        $stmt->execute([$tr]);
        $exists = (bool)$stmt->fetchColumn();
        if (!$exists) {
            $allOk = false;
        }
        echo '<tr><td>' . h($tr) . '</td><td class="' . ($exists ? 'ok' : 'bad') . '">' . ($exists ? 'OK' : 'MISSING') . '</td></tr>';
    }
    echo '</table></div>';

    // Procedures
    echo '<div class="section"><h3>Stored Procedures</h3><table><tr><th>Procedure</th><th>Status</th></tr>';
    foreach ($requiredProcedures as $p) {
        $stmt = $pdo->prepare("SELECT ROUTINE_NAME FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE='PROCEDURE' AND ROUTINE_NAME = ?");
        $stmt->execute([$p]);
        $exists = (bool)$stmt->fetchColumn();
        if (!$exists) {
            $allOk = false;
        }
        echo '<tr><td>' . h($p) . '</td><td class="' . ($exists ? 'ok' : 'bad') . '">' . ($exists ? 'OK' : 'MISSING') . '</td></tr>';
    }
    echo '</table></div>';

    echo '<div class="section">';
    echo '<h3>Overall</h3>';
    echo '<p class="' . ($allOk ? 'ok' : 'bad') . '"><strong>' . ($allOk ? '✅ Schema looks OK for this application.' : '❌ Schema is missing items. Please re-import the SQL or run migrations.') . '</strong></p>';
    echo '</div>';

    echo '</body></html>';

} catch (Exception $e) {
    http_response_code(500);
    echo '<h2 class="bad">Schema Verification Error</h2>';
    echo '<pre>' . h($e->getMessage()) . '</pre>';
}
