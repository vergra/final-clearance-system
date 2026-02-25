<?php
require_once 'config/database.php';
$pdo = getDB();
$stmt = $pdo->query('SELECT subject_id, subject_name, strand_id FROM subjects WHERE subject_name LIKE "Calculus%" ORDER BY subject_id');
while($r=$stmt->fetch()){
    echo $r['subject_id'].' '.$r['subject_name'].' strand_id='.$r['strand_id'].PHP_EOL;
}
