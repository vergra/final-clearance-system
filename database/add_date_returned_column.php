<?php
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

try {
    // Add date_returned column to clearance_status table
    $sql = "ALTER TABLE clearance_status ADD COLUMN date_returned DATE NULL AFTER date_cleared";
    $pdo->exec($sql);
    
    echo "Successfully added 'date_returned' column to clearance_status table.\n";
    
    // Update existing declined records to have date_returned set to their date_cleared
    $sql = "UPDATE clearance_status SET date_returned = date_cleared WHERE status = 'Declined' AND date_cleared IS NOT NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    echo "Updated existing declined records with date_returned values.\n";
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column 'date_returned' already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
