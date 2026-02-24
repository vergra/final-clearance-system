<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDB();
    
    // Create student record for LRN 126956090001
    $lrn = '126956090001';
    $surname = 'Singson';
    $given_name = 'Clydene Franz';
    $middle_name = '';
    $strand = 'STEM';
    $block_code = 'STEM-12A';
    
    echo "<h1>Create Student Record</h1>";
    
    // Check if student already exists
    $stmt = $pdo->prepare("SELECT lrn FROM students WHERE lrn = ?");
    $stmt->execute([$lrn]);
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "<p style='color: orange;'>⚠️ Student with LRN $lrn already exists</p>";
    } else {
        // We need to get department and strand IDs first
        $deptStmt = $pdo->prepare("SELECT department_id FROM departments WHERE department_name LIKE '%STEM%' OR department_name LIKE '%Senior%' LIMIT 1");
        $deptStmt->execute();
        $department = $deptStmt->fetch();
        
        $strandStmt = $pdo->prepare("SELECT strand_id FROM strands WHERE strand_name = ? LIMIT 1");
        $strandStmt->execute([$strand]);
        $strandRecord = $strandStmt->fetch();
        
        $blockStmt = $pdo->prepare("SELECT block_code FROM blocks WHERE block_code = ? LIMIT 1");
        $blockStmt->execute([$block_code]);
        $block = $blockStmt->fetch();
        
        if (!$department) {
            echo "<p style='color: red;'>❌ No department found. Creating default department...</p>";
            $pdo->exec("INSERT INTO departments (department_name) VALUES ('Senior High School')");
            $department_id = $pdo->lastInsertId();
        } else {
            $department_id = $department['department_id'];
        }
        
        if (!$strandRecord) {
            echo "<p style='color: red;'>❌ No strand found. Creating default strand...</p>";
            $pdo->exec("INSERT INTO strands (strand_name, department_id) VALUES ('STEM', $department_id)");
            $strand_id = $pdo->lastInsertId();
        } else {
            $strand_id = $strandRecord['strand_id'];
        }
        
        if (!$block) {
            echo "<p style='color: red;'>❌ No block found. Creating default block...</p>";
            $pdo->exec("INSERT INTO blocks (block_code, department_id) VALUES ('$block_code', $department_id)");
        }
        
        // Get current school year
        $schoolYearStmt = $pdo->query("SELECT school_year_id FROM school_year ORDER BY year_label DESC LIMIT 1");
        $schoolYear = $schoolYearStmt->fetch();
        $school_year_id = $schoolYear ? $schoolYear['school_year_id'] : 1;
        
        // Insert student record
        $stmt = $pdo->prepare("
            INSERT INTO students (lrn, surname, given_name, middle_name, strand, block_code) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$lrn, $surname, $given_name, $middle_name, $strand, $block_code]);
        
        echo "<p style='color: green;'>✅ Student record created successfully!</p>";
        echo "<p><strong>LRN:</strong> $lrn</p>";
        echo "<p><strong>Name:</strong> $surname, $given_name $middle_name</p>";
        echo "<p><strong>Strand:</strong> $strand</p>";
        echo "<p><strong>Block:</strong> $block_code</p>";
    }
    
    // Verify the student was created
    $stmt = $pdo->prepare("SELECT * FROM students WHERE lrn = ?");
    $stmt->execute([$lrn]);
    $student = $stmt->fetch();
    
    if ($student) {
        echo "<h2>✅ Student Record Verified:</h2>";
        echo "<pre>";
        print_r($student);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='../student/request_clearance.php'>← Try Clearance Request Again</a></p>";
?>
