<!DOCTYPE html>
<html>
<head>
    <title>Database Migration</title>
</head>
<body>
    <h1>Add subject_id Column Migration</h1>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=student_clearance', 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            
            echo "Checking if subject_id column exists in clearance_status table...<br>";
            
            // Check if column already exists
            $stmt = $pdo->prepare("SHOW COLUMNS FROM clearance_status LIKE 'subject_id'");
            $stmt->execute();
            $columnExists = $stmt->fetch();
            
            if ($columnExists) {
                echo "<p style='color: green;'>✓ subject_id column already exists!</p>";
            } else {
                echo "<p>Adding subject_id column to clearance_status table...</p>";
                
                // Add the column
                $pdo->exec("ALTER TABLE clearance_status ADD COLUMN subject_id INT NOT NULL DEFAULT 0 AFTER teacher_id");
                echo "<p style='color: green;'>✓ Column added successfully!</p>";
                
                echo "<p>Adding foreign key constraint...</p>";
                
                // Add foreign key constraint
                $pdo->exec("ALTER TABLE clearance_status ADD CONSTRAINT fk_clearance_status_subject FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE RESTRICT");
                echo "<p style='color: green;'>✓ Foreign key constraint added!</p>";
                
                echo "<p style='color: green; font-weight: bold;'>Migration completed successfully!</p>";
            }
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        }
    } else {
    ?>
    
    <form method="post">
        <p>This migration will add the missing 'subject_id' column to the clearance_status table.</p>
        <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer;">Run Migration</button>
    </form>
    
    <?php } ?>
    
    <p><a href="../student/request_clearance.php">← Back to Clearance Form</a></p>
</body>
</html>
