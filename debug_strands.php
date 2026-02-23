<?php
require_once 'config/database.php';
$pdo = getDB();

echo '<h3>All subjects with department info</h3>';
echo '<table border="1"><tr><th>Dept ID</th><th>Department</th><th>Strand</th><th>Subject</th></tr>';
$stmt = $pdo->query('SELECT s.subject_name, s.strand, s.department_id, d.department_name FROM subjects s LEFT JOIN departments d ON s.department_id = d.department_id ORDER BY s.department_id, s.strand');
while ($row = $stmt->fetch()) {
    echo sprintf('<tr><td>%d</td><td>%s</td><td>%s</td><td>%s</td></tr>', 
        $row['department_id'], 
        htmlspecialchars($row['department_name'] ?? 'UNKNOWN'), 
        htmlspecialchars($row['strand']), 
        htmlspecialchars($row['subject_name'])
    );
}
echo '</table>';

echo '<h3>Departments</h3>';
echo '<table border="1"><tr><th>Dept ID</th><th>Department</th></tr>';
$stmt = $pdo->query('SELECT department_id, department_name FROM departments ORDER BY department_id');
while ($row = $stmt->fetch()) {
    echo sprintf('<tr><td>%d</td><td>%s</td></tr>', 
        $row['department_id'], 
        htmlspecialchars($row['department_name'])
    );
}
echo '</table>';
?>
