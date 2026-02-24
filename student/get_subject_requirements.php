<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');

$subject_id = (int)($_GET['subject_id'] ?? 0);
$teacher_id = (int)($_GET['teacher_id'] ?? 0);

if (!$subject_id || !$teacher_id) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Subject ID and Teacher ID are required']);
    exit;
}

try {
    $pdo = getDB();
    
    // Get subject info to find department
    $stmt = $pdo->prepare("SELECT department_id FROM subjects WHERE subject_id = ?");
    $stmt->execute([$subject_id]);
    $subject = $stmt->fetch();
    
    if (!$subject) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Subject not found']);
        exit;
    }
    
    $department_id = $subject['department_id'];
    
    // Get general requirements for the department
    $stmt = $pdo->prepare("
        SELECT requirement_id, requirement_name 
        FROM requirements 
        WHERE department_id = ? 
        ORDER BY requirement_name
    ");
    $stmt->execute([$department_id]);
    $requirements = $stmt->fetchAll();
    
    // If no requirements exist, create some default ones for this subject
    if (empty($requirements)) {
        $defaultRequirements = [
            'Completed all assignments',
            'Submitted all projects',
            'Attended required classes',
            'Returned school materials'
        ];
        
        foreach ($defaultRequirements as $reqName) {
            $stmt = $pdo->prepare("
                INSERT INTO requirements (requirement_name, department_id) 
                VALUES (?, ?)
            ");
            $stmt->execute([$reqName, $department_id]);
        }
        
        // Fetch again
        $stmt->execute([$department_id]);
        $requirements = $stmt->fetchAll();
    }
    
    // Add descriptions to requirements
    $requirementsWithDescriptions = [];
    foreach ($requirements as $req) {
        $requirementsWithDescriptions[] = [
            'requirement_id' => $req['requirement_id'],
            'requirement_name' => $req['requirement_name'],
            'description' => getRequirementDescription($req['requirement_name'])
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($requirementsWithDescriptions);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

function getRequirementDescription($requirementName) {
    $descriptions = [
        'Completed all assignments' => 'All homework and class assignments must be submitted and graded.',
        'Submitted all projects' => 'All individual and group projects must be completed and submitted.',
        'Attended required classes' => 'Minimum attendance requirement must be met for this subject.',
        'Returned school materials' => 'All borrowed books, equipment, and materials must be returned.',
        'Library Clearance' => 'All library books must be returned and fines paid.',
        'Laboratory Clearance' => 'All lab equipment must be cleaned and returned properly.',
        'Equipment Clearance' => 'All borrowed equipment must be returned in good condition.',
        'Project Clearance' => 'All projects must be completed and approved.',
        'Computer Lab Clearance' => 'All computer usage fees paid and files saved properly.',
        'Business Office Clearance' => 'All financial obligations settled.',
        'Club Clearance' => 'All club activities and requirements completed.',
        'Workshop Clearance' => 'All workshop tools and materials returned.',
        'Internship Clearance' => 'All internship requirements and documentation completed.',
        'Faculty Clearance' => 'All faculty requirements and consultations completed.'
    ];
    
    return $descriptions[$requirementName] ?? 'Please contact the teacher for specific requirements.';
}
?>
