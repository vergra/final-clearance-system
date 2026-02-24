<?php
// Script to add sample requirements for testing
// Run this once to add basic requirements to existing departments

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDB();
    
    // Get existing departments
    $departments = $pdo->query("SELECT department_id, department_name FROM departments")->fetchAll();
    
    if (empty($departments)) {
        echo "No departments found. Please add departments first via admin interface.\n";
        exit;
    }
    
    // Sample requirements for each department
    $sampleRequirements = [
        'Academic' => ['Library Clearance', 'Classroom Clearance', 'Laboratory Clearance', 'Faculty Clearance'],
        'STEM' => ['Library Clearance', 'Laboratory Clearance', 'Equipment Clearance', 'Project Clearance'],
        'ABM' => ['Library Clearance', 'Computer Lab Clearance', 'Business Office Clearance', 'Faculty Clearance'],
        'HUMSS' => ['Library Clearance', 'Guidance Office Clearance', 'Club Clearance', 'Faculty Clearance'],
        'TVL' => ['Workshop Clearance', 'Equipment Clearance', 'Internship Clearance', 'Faculty Clearance']
    ];
    
    foreach ($departments as $dept) {
        $deptName = $dept['department_name'];
        $deptId = $dept['department_id'];
        
        // Check if requirements already exist for this department
        $existing = $pdo->prepare("SELECT COUNT(*) FROM requirements WHERE department_id = ?");
        $existing->execute([$deptId]);
        
        if ($existing->fetchColumn() > 0) {
            echo "Requirements already exist for department: $deptName\n";
            continue;
        }
        
        // Add requirements based on department type
        $requirementsToAdd = [];
        foreach ($sampleRequirements as $key => $reqs) {
            if (stripos($deptName, $key) !== false) {
                $requirementsToAdd = $reqs;
                break;
            }
        }
        
        // If no match, use generic requirements
        if (empty($requirementsToAdd)) {
            $requirementsToAdd = ['Library Clearance', 'Faculty Clearance', 'Admin Office Clearance'];
        }
        
        // Insert requirements
        foreach ($requirementsToAdd as $reqName) {
            $stmt = $pdo->prepare("INSERT INTO requirements (requirement_name, department_id) VALUES (?, ?)");
            $stmt->execute([$reqName, $deptId]);
            echo "Added requirement '$reqName' to department '$deptName'\n";
        }
    }
    
    echo "\nSample requirements added successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
