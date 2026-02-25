<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['student']);

require_once __DIR__ . '/../config/database.php';
$pdo = getDB();
$currentUser = getCurrentUser();

// Dompdf
require_once __DIR__ . '/../assets/vendor/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$lrn = (string)($currentUser['reference_id'] ?? '');
if ($lrn === '') {
    http_response_code(400);
    echo 'Missing student reference id.';
    exit;
}

$requestGroupId = isset($_GET['request_group_id']) ? trim((string)$_GET['request_group_id']) : '';
$dateSubmitted = isset($_GET['date_submitted']) ? trim((string)$_GET['date_submitted']) : '';
$schoolYearId = isset($_GET['school_year_id']) ? (int)$_GET['school_year_id'] : 0;

if ($requestGroupId === '' && ($dateSubmitted === '' || $schoolYearId === 0)) {
    http_response_code(400);
    echo 'Missing form identifier.';
    exit;
}

// Student details
$stmt = $pdo->prepare("SELECT * FROM students WHERE lrn = ?");
$stmt->execute([$lrn]);
$student = $stmt->fetch();
if (!$student) {
    http_response_code(404);
    echo 'Student not found.';
    exit;
}

// Fetch the clearance rows for just this form
$params = [$lrn];
$where = "c.lrn = ?";

if ($requestGroupId !== '') {
    $where .= " AND c.request_group_id = ?";
    $params[] = $requestGroupId;
} else {
    $where .= " AND c.school_year_id = ? AND c.date_submitted = ?";
    $params[] = $schoolYearId;
    $params[] = $dateSubmitted;
}

$stmt = $pdo->prepare("
    SELECT c.clearance_id, c.lrn, c.requirement_id, c.teacher_id, c.subject_id, c.school_year_id,
           c.status, c.date_submitted, c.date_cleared, c.date_returned, c.remarks, c.request_group_id,
           r.requirement_name, sub.subject_name, sy.year_label,
           d.department_name, st.strand_name,
           t.surname AS t_surname, t.given_name AS t_given
    FROM clearance_status c
    JOIN requirements r ON r.requirement_id = c.requirement_id
    JOIN subjects sub ON sub.subject_id = c.subject_id
    JOIN school_year sy ON sy.school_year_id = c.school_year_id
    LEFT JOIN teachers t ON t.teacher_id = c.teacher_id
    LEFT JOIN departments d ON d.department_id = t.department_id
    LEFT JOIN strands st ON st.strand_id = sub.strand_id
    WHERE $where
    ORDER BY c.clearance_id ASC
");
$stmt->execute($params);
$clearances = $stmt->fetchAll();

if (!$clearances) {
    http_response_code(404);
    echo 'No clearance form found.';
    exit;
}

// Derive labels
$yearLabel = (string)($clearances[0]['year_label'] ?? '');
$departmentName = (string)($clearances[0]['department_name'] ?? '');
$strandName = (string)($clearances[0]['strand_name'] ?? '');
$formDate = (string)($clearances[0]['date_submitted'] ?? date('Y-m-d'));

// Build HTML
$title = 'Student Clearance Form';
$studentName = trim(($student['surname'] ?? '') . ', ' . ($student['given_name'] ?? '') . ' ' . ($student['middle_name'] ?? ''));
$block = (string)($student['block_code'] ?? '');

$rowsHtml = '';
foreach ($clearances as $c) {
    $teacherName = trim(($c['t_surname'] ?? '') . ', ' . ($c['t_given'] ?? ''));
    $subjectName = (string)($c['subject_name'] ?? '');
    $status = (string)($c['status'] ?? '');
    $dateCleared = (string)($c['date_cleared'] ?? '');
    $remarks = (string)($c['remarks'] ?? '');

    $statusText = htmlspecialchars($status);
    if ($status === 'Approved' && $dateCleared !== '') {
        $statusText .= ' (Cleared on ' . htmlspecialchars($dateCleared) . ')';
    } elseif ($status === 'Declined' && $remarks !== '') {
        $statusText .= ' (Return for Compliance)';
    }

    $rowsHtml .= '<tr>'
        . '<td>' . htmlspecialchars($teacherName) . '</td>'
        . '<td>' . htmlspecialchars($subjectName) . '</td>'
        . '<td>' . $statusText . '</td>'
        . '</tr>';
}

$html = '<!doctype html><html><head><meta charset="utf-8">'
    . '<style>'
    . '@page { margin: 18mm; }'
    . 'body { font-family: Times New Roman, serif; font-size: 12pt; color: #000; }'
    . '.header { text-align:center; margin-bottom: 14px; }'
    . '.title { font-size: 16pt; font-weight: bold; }'
    . '.sub { font-size: 11pt; }'
    . '.box { border: 1px solid #000; padding: 10px; }'
    . '.meta { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 10px; }'
    . '.meta td { padding: 4px 0; }'
    . 'table { width: 100%; border-collapse: collapse; }'
    . 'th, td { border: 1px solid #000; padding: 6px; vertical-align: top; }'
    . 'th { background: #f2f2f2; }'
    . '.sign-row { width: 100%; margin-top: 22px; }'
    . '.sign { width: 48%; display: inline-block; margin-right: 2%; }'
    . '.line { border-bottom: 1px solid #000; height: 18px; }'
    . '.label { font-size: 10pt; margin-top: 4px; }'
    . '</style>'
    . '</head><body>'
    . '<div class="header">'
    . '<div class="title">' . htmlspecialchars($title) . '</div>'
    . '<div class="sub">Senior High School Clearance</div>'
    . '<div class="sub">School Year: ' . htmlspecialchars($yearLabel) . ($departmentName !== '' ? ' - ' . htmlspecialchars($departmentName) : '') . '</div>'
    . '</div>'
    . '<div class="box">'
    . '<table class="meta">'
    . '<tr><td><strong>Name:</strong> ' . htmlspecialchars($studentName) . '</td><td><strong>Grade Block:</strong> ' . htmlspecialchars($block) . '</td></tr>'
    . '<tr><td><strong>LRN:</strong> ' . htmlspecialchars($lrn) . '</td><td><strong>Strand:</strong> ' . htmlspecialchars($strandName) . '</td></tr>'
    . '<tr><td><strong>Date Submitted:</strong> ' . htmlspecialchars($formDate) . '</td><td></td></tr>'
    . '</table>'
    . '<table>'
    . '<thead><tr><th>Teacher</th><th>Subject</th><th>Status</th></tr></thead>'
    . '<tbody>' . $rowsHtml . '</tbody>'
    . '</table>'
    . '<div class="sign-row">'
    . '<div class="sign"><div class="line"></div><div class="label">Guidance Signature</div></div>'
    . '<div class="sign"><div class="line"></div><div class="label">Principal Signature</div></div>'
    . '</div>'
    . '<div class="sign-row">'
    . '<div class="sign"><div class="line"></div><div class="label">Registrar Signature</div></div>'
    . '<div class="sign"><div class="line"></div><div class="label">Adviser Signature</div></div>'
    . '</div>'
    . '</div>'
    . '</body></html>';

function renderWithPaper(string $html, string $paper): array {
    $options = new Options();
    $options->set('isRemoteEnabled', false);
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->setPaper($paper, 'portrait');
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->render();

    $canvas = $dompdf->getCanvas();
    $pageCount = method_exists($canvas, 'get_page_count') ? (int)$canvas->get_page_count() : 1;

    return [$dompdf, $pageCount];
}

// Auto choose paper (best-effort)
[$dompdf, $pages] = renderWithPaper($html, 'a4');
$paperUsed = 'A4';

if ($pages > 1) {
    [$dompdf, $pages] = renderWithPaper($html, 'letter');
    $paperUsed = 'Letter';
}

if ($pages > 1) {
    [$dompdf, $pages] = renderWithPaper($html, 'legal');
    $paperUsed = 'Legal';
}

$filename = 'Clearance_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $lrn) . '_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $yearLabel) . '_' . $paperUsed . '.pdf';

// Stream
$pdfOutput = $dompdf->output();
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($pdfOutput));
echo $pdfOutput;
exit;
