<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDB();

    echo "<h1>Migration: request_group_id for clearance_status</h1>";

    // Check column exists
    $col = $pdo->query("SHOW COLUMNS FROM clearance_status LIKE 'request_group_id'")->fetch();
    if (!$col) {
        echo "<p>Adding column request_group_id...</p>";
        $pdo->exec("ALTER TABLE clearance_status ADD COLUMN request_group_id VARCHAR(36) DEFAULT NULL");
        echo "<p style='color: green;'>✅ Column added.</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Column request_group_id already exists.</p>";
    }

    // Backfill existing rows: group by lrn + school_year_id + date_submitted
    echo "<p>Backfilling existing clearance_status rows...</p>";

    $rows = $pdo->query("SELECT DISTINCT lrn, school_year_id, date_submitted FROM clearance_status WHERE request_group_id IS NULL")->fetchAll();

    $updated = 0;
    foreach ($rows as $r) {
        $lrn = $r['lrn'];
        $sy = (int)$r['school_year_id'];
        $ds = $r['date_submitted'];
        if ($ds === null) {
            continue;
        }
        $uuid = bin2hex(random_bytes(16));
        $stmt = $pdo->prepare("UPDATE clearance_status SET request_group_id = ? WHERE request_group_id IS NULL AND lrn = ? AND school_year_id = ? AND date_submitted = ?");
        $stmt->execute([$uuid, $lrn, $sy, $ds]);
        $updated += $stmt->rowCount();
    }

    echo "<p style='color: green;'>✅ Updated $updated rows.</p>";

    echo "<p><a href='../student/my_clearance.php'>Go to My Clearance</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
