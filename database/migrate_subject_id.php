<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDB();
    
    echo "Checking if subject_id column exists in clearance_status table...\n";
    
    // Check if column already exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM clearance_status LIKE 'subject_id'");
    $stmt->execute();
    $columnExists = $stmt->fetch();
    
    if ($columnExists) {
        echo "subject_id column already exists!\n";
    } else {
        echo "Adding subject_id column to clearance_status table...\n";
        
        // Add the column
        $pdo->exec("ALTER TABLE clearance_status ADD COLUMN subject_id INT NOT NULL DEFAULT 0 AFTER teacher_id");
        
        echo "Adding foreign key constraint...\n";
        
        // Add foreign key constraint
        $pdo->exec("ALTER TABLE clearance_status ADD CONSTRAINT fk_clearance_status_subject FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE RESTRICT");
        
        echo "Migration completed successfully!\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
