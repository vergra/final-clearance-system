<?php
require_once 'config/database.php';
$pdo = getDB();
$stmt = $pdo->query('SELECT strand_id, strand_name, department_id FROM strands WHERE strand_name LIKE "TVL%" ORDER BY strand_id');
while($r=$stmt->fetch()){
    echo $r['strand_id'].' '.$r['strand_name'].' dept_id='.$r['department_id'].PHP_EOL;
}
