<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDB();
    
    echo "<h1>Create Teacher-Subject Assignment Table</h1>";
    
    // Check if table already exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'teacher_subject'");
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "<p style='color: orange;'>⚠️ teacher_subject table already exists</p>";
    } else {
        echo "<p>Creating teacher_subject table...</p>";
        
        $pdo->exec("
            CREATE TABLE teacher_subject (
                teacher_subject_id INT AUTO_INCREMENT PRIMARY KEY,
                teacher_id INT NOT NULL,
                subject_id INT NOT NULL,
                school_year_id INT NOT NULL,
                assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE,
                FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
                FOREIGN KEY (school_year_id) REFERENCES school_year(school_year_id) ON DELETE CASCADE,
                UNIQUE KEY unique_teacher_subject_year (teacher_id, subject_id, school_year_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        echo "<p style='color: green;'>✅ teacher_subject table created successfully!</p>";
    }
    
    // Get current school year
    $schoolYearStmt = $pdo->query("SELECT school_year_id, year_label FROM school_year ORDER BY year_label DESC LIMIT 1");
    $currentSchoolYear = $schoolYearStmt->fetch();
    
    if ($currentSchoolYear) {
        echo "<p>Current school year: " . $currentSchoolYear['year_label'] . "</p>";
        
        // Migrate existing teacher-subject relationships from teachers table
        echo "<p>Migrating existing teacher-subject assignments...</p>";
        
        $stmt = $pdo->query("
            INSERT INTO teacher_subject (teacher_id, subject_id, school_year_id)
            SELECT teacher_id, subject_id, " . $currentSchoolYear['school_year_id'] . "
            FROM teachers 
            WHERE subject_id IS NOT NULL
            ON DUPLICATE KEY UPDATE assigned_at = CURRENT_TIMESTAMP
        ");
        
        $migratedCount = $stmt->rowCount();
        echo "<p style='color: green;'>✅ Migrated $migratedCount teacher-subject assignments</p>";
    }
    
    echo "<h2>✅ Migration Complete!</h2>";
    echo "<p>The teacher-subject assignment system is now ready.</p>";
    echo "<p><a href='../public/signup_teacher.php'>← Test Teacher Signup</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
