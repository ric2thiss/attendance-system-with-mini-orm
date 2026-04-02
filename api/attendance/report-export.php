<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../auth/helpers.php';

if (!isAuthenticated()) {
    http_response_code(401);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Unauthorized';
    exit;
}

$format = strtolower(trim((string) ($_GET['format'] ?? 'csv')));
if (!in_array($format, ['csv', 'doc', 'html'], true)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Invalid format (use csv, doc, or html)';
    exit;
}

$mode = strtolower(trim((string) ($_GET['mode'] ?? 'logs'))) === 'event' ? 'event' : 'logs';
$search = isset($_GET['search']) ? trim((string) $_GET['search']) : '';
$fromDate = isset($_GET['from']) ? trim((string) $_GET['from']) : null;
$toDate = isset($_GET['to']) ? trim((string) $_GET['to']) : null;
if ($fromDate === '') {
    $fromDate = null;
}
if ($toDate === '') {
    $toDate = null;
}

$sort = (string) ($_GET['sort'] ?? 'timestamp');
$order = (string) ($_GET['order'] ?? 'desc');
$allowedSort = ['timestamp', 'employee_id', 'full_name'];
if (!in_array($sort, $allowedSort, true)) {
    $sort = 'timestamp';
}
$order = strtolower($order) === 'asc' ? 'asc' : 'desc';

$activityFilter = null;
$rawAct = isset($_GET['activity_id']) ? trim((string) $_GET['activity_id']) : '';
if ($rawAct === '0') {
    $activityFilter = 0;
} elseif ($rawAct !== '' && ctype_digit($rawAct)) {
    $activityFilter = (int) $rawAct;
}

$eventId = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;

$pdo = (new Database())->connect();
$attRepo = new AttendanceRepository($pdo);
$repRepo = new AttendanceReportRepository($pdo);

$logoUrl = rtrim(BASE_URL, '/') . '/utils/img/logo.png';
$title = 'Attendance Report';
$rangeLabel = '';

$rows = [];
$headers = [];

if ($mode === 'event' && $eventId > 0) {
    $rosterSort = $sort === 'employee_id' ? 'employee_id' : 'name';
    $bundle = $repRepo->getEventRosterPage($eventId, $search, $rosterSort, $order, 1, 20000);
    $act = $bundle['activity'] ?? null;
    if (!$act) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Event not found';
        exit;
    }
    $rangeLabel = ($act['activity_date'] ?? '') . ' · ' . ($act['name'] ?? '');
    $headers = ['Employee ID', 'Employee Name', 'Date', 'Time In', 'Time Out', 'Status', 'Event'];
    foreach ($bundle['rows'] as $r) {
        $rows[] = [
            $r['employee_id'],
            $r['full_name'],
            $r['date'],
            $r['time_in'],
            $r['time_out'],
            $r['status'],
            $r['event_name'],
        ];
    }
} else {
    $rangeLabel = trim(($fromDate ?? '') . ' to ' . ($toDate ?? ''));
    $list = $attRepo->getReportLogsExport($search, $fromDate, $toDate, $activityFilter, $sort, $order, 5000);
    $headers = ['Employee ID', 'Employee Name', 'Date', 'Time In', 'Time Out', 'Status', 'Event'];
    foreach ($list as $att) {
        $a = is_object($att) ? json_decode(json_encode($att), true) : $att;
        $ts = (string) ($a['attendance_time'] ?? '');
        $datePart = '—';
        $timePart = '—';
        if ($ts !== '') {
            try {
                $dt = new DateTime($ts, new DateTimeZone('Asia/Manila'));
                $datePart = $dt->format('Y-m-d');
                $timePart = $dt->format('h:i:s A');
            } catch (Exception $e) {
                $timePart = $ts;
            }
        }
        $wl = (string) ($a['window_label'] ?? $a['window'] ?? '');
        $norm = AttendanceAnalyticsService::normalizeLabel($wl);
        $timeIn = preg_match('/_in$/', $norm) ? $timePart : '—';
        $timeOut = preg_match('/_out$/', $norm) ? $timePart : '—';
        if ($timeIn === '—' && $timeOut === '—') {
            $timeIn = $timePart;
        }
        $status = $wl !== '' ? $wl : 'Logged';
        $rows[] = [
            (string) ($a['employee_id'] ?? ''),
            (string) ($a['full_name'] ?? ''),
            $datePart,
            $timeIn,
            $timeOut,
            $status,
            (string) ($a['activity_name'] ?? '—'),
        ];
    }
}

function h($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="attendance_report_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($out, [$title]);
    fputcsv($out, ['Period / event: ' . $rangeLabel]);
    fputcsv($out, []);
    fputcsv($out, $headers);
    foreach ($rows as $r) {
        fputcsv($out, $r);
    }
    fclose($out);
    exit;
}

$htmlTable = '<table border="1" cellpadding="4" cellspacing="0"><thead><tr>';
foreach ($headers as $hcol) {
    $htmlTable .= '<th>' . h($hcol) . '</th>';
}
$htmlTable .= '</tr></thead><tbody>';
foreach ($rows as $r) {
    $htmlTable .= '<tr>';
    foreach ($r as $cell) {
        $htmlTable .= '<td>' . h($cell) . '</td>';
    }
    $htmlTable .= '</tr>';
}
$htmlTable .= '</tbody></table>';

$docHeadXml = '<!--[if gte mso 9]><xml><w:WordDocument><w:View>Print</w:View><w:Zoom>100</w:Zoom><w:Orientation>Landscape</w:Orientation></w:WordDocument></xml><![endif]-->';
$docStyles = '<style>@page Section1 { size: 11in 8.5in; mso-page-orientation: landscape; } div.Section1 { page: Section1; }</style>';
$docHeader = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word"><head><meta charset="utf-8"><title>' . h($title) . '</title>' . $docStyles . '</head><body>' . $docHeadXml;
$docHeader .= '<div class="Section1"><p><strong>' . h($title) . '</strong><br>Period: ' . h($rangeLabel) . '</p>';
$docHeader .= $htmlTable . '</div></body></html>';

if ($format === 'doc') {
    header('Content-Type: application/msword; charset=utf-8');
    header('Content-Disposition: attachment; filename="attendance_report_' . date('Y-m-d') . '.doc"');
    echo $docHeader;
    exit;
}

// html = printable plain view
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= h($title) ?></title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: Arial, sans-serif; margin: 24px; color: #000; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        .meta { margin-bottom: 16px; font-size: 14px; }
        table { border-collapse: collapse; width: 100%; font-size: 11px; }
        th, td { border: 1px solid #333; padding: 5px 7px; text-align: left; }
        th { font-weight: bold; }
        .logo { max-height: 56px; margin-bottom: 10px; }
        @media print {
            .no-print { display: none !important; }
            @page { size: A4 landscape; margin: 10mm; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:12px;">
        <button type="button" onclick="window.print()">Print</button>
    </div>
    <img class="logo" src="<?= h($logoUrl) ?>" alt="Logo">
    <h1><?= h($title) ?></h1>
    <div class="meta">Selected range / event: <?= h($rangeLabel) ?></div>
    <?= $htmlTable ?>
</body>
</html>
