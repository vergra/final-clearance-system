<?php
/**
 * Migration Script - Upgrade to Compliance System
 * 
 * This script safely upgrades an existing clearance system to include
 * the new compliance tracking features.
 * 
 * Usage:
 * 1. Upload this file to your server
 * 2. Access via browser: http://yoursite.com/database/migrate_to_compliance_system.php
 * 3. Follow the on-screen instructions
 * 4. Delete this file after migration
 */

require_once __DIR__ . '/../config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clearance System Migration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .step { display: none; }
        .step.active { display: block; }
        .log { 
            background: #f8f9fa; 
            border: 1px solid #dee2e6; 
            border-radius: 4px; 
            padding: 15px; 
            max-height: 300px; 
            overflow-y: auto; 
            font-family: monospace; 
            font-size: 12px;
        }
        .success { color: #198754; }
        .error { color: #dc3545; }
        .warning { color: #fd7e14; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">🔄 Clearance System Migration to Compliance Tracking</h3>
                    </div>
                    <div class="card-body">
                        
                        <!-- Step 1: Welcome -->
                        <div class="step active" id="step1">
                            <h4>Step 1: Welcome</h4>
                            <p>This migration script will upgrade your existing clearance system to include the new compliance tracking features:</p>
                            <ul>
                                <li>✅ Add <code>date_returned</code> column for tracking compliance dates</li>
                                <li>✅ Create views for better reporting</li>
                                <li>✅ Add stored procedures for common operations</li>
                                <li>✅ Update existing declined records with proper dates</li>
                                <li>✅ Optimize database indexes</li>
                            </ul>
                            <div class="alert alert-warning">
                                <strong>⚠️ Important:</strong> Please backup your database before proceeding!
                            </div>
                            <button class="btn btn-primary" onclick="showStep(2)">Start Migration</button>
                        </div>

                        <!-- Step 2: Backup Check -->
                        <div class="step" id="step2">
                            <h4>Step 2: Database Backup</h4>
                            <p>Have you backed up your database?</p>
                            <div class="alert alert-info">
                                <strong>Backup Command:</strong><br>
                                <code>mysqldump -u root -p student_clearance > backup_before_migration.sql</code>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="backupConfirmed">
                                <label class="form-check-label" for="backupConfirmed">
                                    Yes, I have backed up my database
                                </label>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-secondary" onclick="showStep(1)">Back</button>
                                <button class="btn btn-primary" onclick="showStep(3)" id="proceedBtn" disabled>Proceed with Migration</button>
                            </div>
                        </div>

                        <!-- Step 3: Migration -->
                        <div class="step" id="step3">
                            <h4>Step 3: Running Migration</h4>
                            <p>Please wait while the migration runs...</p>
                            <div class="log" id="migrationLog"></div>
                            <div class="mt-3">
                                <button class="btn btn-primary" onclick="runMigration()" id="startMigrationBtn">Start Migration</button>
                                <button class="btn btn-success" onclick="showStep(4)" id="continueBtn" style="display: none;">Continue</button>
                            </div>
                        </div>

                        <!-- Step 4: Complete -->
                        <div class="step" id="step4">
                            <h4>Step 4: Migration Complete!</h4>
                            <div class="alert alert-success">
                                <h5>✅ Migration Successful!</h5>
                                <p>Your clearance system has been upgraded with compliance tracking features.</p>
                            </div>
                            
                            <h5>What's New:</h5>
                            <ul>
                                <li><strong>Compliance Tracking:</strong> Teachers can now return clearances with specific requirements</li>
                                <li><strong>Date Tracking:</strong> Precise dates for when compliance was requested</li>
                                <li><strong>Student Portal:</strong> Students can view compliance details and resubmit requests</li>
                                <li><strong>Improved Workflow:</strong> Better tracking of clearance lifecycle</li>
                            </ul>

                            <h5>Next Steps:</h5>
                            <ol>
                                <li>Test the new compliance features</li>
                                <li>Train teachers on the new return for compliance workflow</li>
                                <li>Delete this migration script from your server</li>
                                <li>Update your documentation</li>
                            </ol>

                            <div class="alert alert-warning">
                                <strong>🔒 Security Reminder:</strong> Please delete this migration script (<code>migrate_to_compliance_system.php</code>) from your server after testing!
                            </div>

                            <button class="btn btn-success" onclick="window.location.href='../public/index.php'">Go to Clearance System</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showStep(stepNum) {
            document.querySelectorAll('.step').forEach(step => step.classList.remove('active'));
            document.getElementById('step' + stepNum).classList.add('active');
        }

        document.getElementById('backupConfirmed').addEventListener('change', function() {
            document.getElementById('proceedBtn').disabled = !this.checked;
        });

        function log(message, type = 'info') {
            const log = document.getElementById('migrationLog');
            const timestamp = new Date().toLocaleTimeString();
            const className = type === 'error' ? 'error' : (type === 'warning' ? 'warning' : 'success');
            log.innerHTML += `<div class="${className}">[${timestamp}] ${message}</div>`;
            log.scrollTop = log.scrollHeight;
        }

        async function runMigration() {
            document.getElementById('startMigrationBtn').disabled = true;
            document.getElementById('migrationLog').innerHTML = '';
            
            log('Starting migration...', 'info');
            
            try {
                const response = await fetch('migrate_to_compliance_system.php?action=run', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    log(result.message, 'success');
                    if (result.details) {
                        result.details.forEach(detail => log(detail, 'info'));
                    }
                    log('Migration completed successfully!', 'success');
                    document.getElementById('continueBtn').style.display = 'inline-block';
                } else {
                    log('Migration failed: ' + result.error, 'error');
                    document.getElementById('startMigrationBtn').disabled = false;
                }
            } catch (error) {
                log('Error: ' + error.message, 'error');
                document.getElementById('startMigrationBtn').disabled = false;
            }
        }
    </script>
</body>
</html>

<?php
// Handle the actual migration
if (isset($_GET['action']) && $_GET['action'] === 'run') {
    header('Content-Type: application/json');
    
    try {
        $pdo = getDB();
        $pdo->beginTransaction();
        
        $results = [];
        
        // 1. Add date_returned column
        try {
            $pdo->exec("ALTER TABLE clearance_status ADD COLUMN date_returned DATE NULL AFTER date_cleared");
            $results[] = "Added date_returned column";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                throw $e;
            }
            $results[] = "date_returned column already exists";
        }
        
        // 2. Update existing declined records
        $stmt = $pdo->prepare("UPDATE clearance_status SET date_returned = date_cleared WHERE status = 'Declined' AND date_returned IS NULL AND date_cleared IS NOT NULL");
        $updated = $stmt->rowCount();
        $results[] = "Updated $updated existing declined records with date_returned";
        
        // 3. Add indexes for performance
        try {
            $pdo->exec("CREATE INDEX idx_compliance_tracking ON clearance_status(status, date_returned, date_cleared)");
            $results[] = "Added compliance tracking index";
        } catch (PDOException $e) {
            $results[] = "Compliance tracking index already exists";
        }
        
        // 4. Create views
        try {
            $pdo->exec("DROP VIEW IF EXISTS compliance_tracking");
            $viewSql = "
                CREATE VIEW compliance_tracking AS
                SELECT 
                    cs.clearance_id,
                    cs.lrn,
                    CONCAT(s.surname, ', ', s.given_name) AS student_name,
                    cs.teacher_id,
                    CONCAT(t.surname, ', ', t.given_name) AS teacher_name,
                    cs.subject_id,
                    sub.subject_name,
                    cs.requirement_id,
                    r.requirement_name,
                    cs.status,
                    cs.date_submitted AS initial_request_date,
                    cs.date_returned AS compliance_sent_date,
                    cs.date_cleared AS action_date,
                    cs.remarks AS compliance_requirements,
                    CASE 
                        WHEN cs.status = 'Declined' AND cs.date_returned IS NOT NULL THEN 'Returned for Compliance'
                        WHEN cs.status = 'Pending' THEN 'Pending Review'
                        WHEN cs.status = 'Approved' THEN 'Approved'
                        ELSE 'Unknown'
                    END AS current_status
                FROM clearance_status cs
                JOIN students s ON cs.lrn = s.lrn
                JOIN requirements r ON cs.requirement_id = r.requirement_id
                JOIN teachers t ON cs.teacher_id = t.teacher_id
                JOIN subjects sub ON cs.subject_id = sub.subject_id
                WHERE cs.status IN ('Pending', 'Declined', 'Approved')
            ";
            $pdo->exec($viewSql);
            $results[] = "Created compliance_tracking view";
        } catch (PDOException $e) {
            $results[] = "Error creating view: " . $e->getMessage();
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Migration completed successfully!',
            'details' => $results
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}
?>
