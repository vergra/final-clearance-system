<?php
$pageTitle = 'Resubmit Clearance';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');

$pdo = getDB();
$user = getCurrentUser();
$lrn = $user['reference_id'];

// Get clearance ID from URL
$clearance_id = (int)($_GET['id'] ?? 0);
if ($clearance_id <= 0) {
    die('Error: Invalid clearance ID.');
}

// Get the original clearance details
$stmt = $pdo->prepare("
    SELECT c.clearance_id, c.teacher_id, c.subject_id, c.requirement_id, c.school_year_id,
           c.remarks, r.requirement_name, sub.subject_name, sy.year_label,
           t.surname AS t_surname, t.given_name AS t_given
    FROM clearance_status c
    JOIN requirements r ON r.requirement_id = c.requirement_id
    JOIN subjects sub ON sub.subject_id = c.subject_id
    JOIN school_year sy ON sy.school_year_id = c.school_year_id
    JOIN teachers t ON t.teacher_id = c.teacher_id
    WHERE c.clearance_id = ? AND c.lrn = ? AND c.status = 'Declined'
");
$stmt->execute([$clearance_id, $lrn]);
$originalClearance = $stmt->fetch();

if (!$originalClearance) {
    die('Error: Clearance record not found or not eligible for resubmission.');
}

// Process resubmission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Update the original declined clearance record back to pending
        $stmt = $pdo->prepare("
            UPDATE clearance_status 
            SET status = 'Pending', 
                date_submitted = CURDATE(), 
                remarks = '',
                date_cleared = NULL,
                date_returned = NULL
            WHERE clearance_id = ?
        ");
        
        $stmt->execute([$clearance_id]);
        
        $pdo->commit();
        
        // Redirect to success page
        header('Location: my_clearance.php?resubmitted=1');
        exit;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Error resubmitting clearance: ' . $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Resubmit Clearance</h1>
    <a href="my_clearance.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to My Clearance
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="bi bi-arrow-clockwise me-2"></i>Update Clearance Request
                </h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <div class="alert alert-info">
                    <h6 class="alert-heading">Original Request Details:</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Teacher:</strong> <?php echo htmlspecialchars($originalClearance['t_surname'] . ', ' . $originalClearance['t_given']); ?></p>
                            <p class="mb-1"><strong>Subject:</strong> <?php echo htmlspecialchars($originalClearance['subject_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Requirement:</strong> <?php echo htmlspecialchars($originalClearance['requirement_name']); ?></p>
                            <p class="mb-1"><strong>School Year:</strong> <?php echo htmlspecialchars($originalClearance['year_label']); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning border border-warning">
                    <h6 class="alert-heading">Requirements to Complete:</h6>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($originalClearance['remarks'])); ?></p>
                </div>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Confirmation:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmRequirements" name="confirmRequirements" value="1" required>
                            <label class="form-check-label" for="confirmRequirements">
                                I have completed all the requirements listed above and am ready to update this clearance request back to pending status.
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-arrow-clockwise me-1"></i>Update Request to Pending
                        </button>
                        <a href="my_clearance.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Important Notes</h6>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Make sure you have completed all requirements before updating</li>
                    <li>This will update your original request back to "Pending" status</li>
                    <li>The same teacher will review your updated request</li>
                    <li>Your compliance history will be preserved in the system</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
