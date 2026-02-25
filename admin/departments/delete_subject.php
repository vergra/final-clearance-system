<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
$strand_id = (int)($_GET['strand_id'] ?? 0);

if (!$id || !$strand_id) {
    header('Location: index.php');
    exit;
}

$error = '';
 $deleted = false;

try {
    // Ensure the subject belongs to the strand
    $stmt = $pdo->prepare('SELECT subject_id FROM subjects WHERE subject_id = ? AND strand_id = ?');
    $stmt->execute([$id, $strand_id]);
    $ok = $stmt->fetchColumn();

    if ($ok) {
        // Check for dependent rows before attempting delete
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM clearance_status WHERE subject_id = ?');
        $stmt->execute([$id]);
        $cClearance = $stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM student_subject WHERE subject_id = ?');
        $stmt->execute([$id]);
        $cStudent = $stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM teacher_subject WHERE subject_id = ?');
        $stmt->execute([$id]);
        $cTeacher = $stmt->fetchColumn();

        if ($cClearance > 0 || $cStudent > 0 || $cTeacher > 0) {
            $reasons = [];
            if ($cClearance) $reasons[] = "$cClearance clearance record(s)";
            if ($cStudent)  $reasons[] = "$cStudent student assignment(s)";
            if ($cTeacher)  $reasons[] = "$cTeacher teacher assignment(s)";
            $error = 'Cannot delete subject because it has dependencies: ' . implode(', ', $reasons) . '. Remove these first.';
        } else {
            $pdo->beginTransaction();
            try {
                // No dependencies: safe to delete
                $stmt = $pdo->prepare('DELETE FROM subjects WHERE subject_id = ?');
                $stmt->execute([$id]);
                $cSubject = $stmt->rowCount();
                $pdo->commit();
                $deleted = ($cSubject > 0);
                error_log("Delete subject_id=$id: removed $cSubject subjects rows (no dependencies).");
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Failed to delete subject due to a database error.';
                error_log("Delete transaction failed for subject_id=$id: " . $e->getMessage());
            }
        }
    } else {
        $error = 'Subject does not belong to this strand.';
    }
} catch (PDOException $e) {
    $error = 'Database error while checking dependencies.';
    error_log("Delete PDO error for subject_id=$id: " . $e->getMessage());
}

$redirect = 'view_strand.php?id=' . $strand_id;
if ($error) {
    $redirect .= '&error=' . urlencode($error);
} elseif ($deleted) {
    $redirect .= '&subject_deleted=1';
} else {
    $redirect .= '&error=' . urlencode('Nothing was deleted.');
}
header('Location: ' . $redirect);
exit;
