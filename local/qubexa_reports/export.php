<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/csvlib.class.php');

require_login();

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(
    new moodle_url('/local/qubexa_reports/export.php')
);

require_capability(
    'local/qubexa_reports:view',
    $context
);

$classid = optional_param(
    'reportclassid',
    0,
    PARAM_INT
);
$studentquery = optional_param(
    'reportstudent',
    '',
    PARAM_TEXT
);
$datefrom = optional_param(
    'reportdatefrom',
    '',
    PARAM_RAW_TRIMMED
);
$dateto = optional_param(
    'reportdateto',
    '',
    PARAM_RAW_TRIMMED
);

$report = new \local_qubexa_reports\output\reports_page(
    (int) $USER->id,
    $classid,
    $studentquery,
    $datefrom,
    $dateto
);

$filename = 'hocadex-katilim-raporu-' .
    userdate(time(), '%Y-%m-%d');
$csv = new csv_export_writer(
    'semicolon',
    '"',
    'text/csv',
    true
);

$csv->set_filename($filename);
$csv->add_data([
    get_string('student', 'local_qubexa_reports'),
    get_string('studentnumber', 'local_qubexa_reports'),
    get_string('class', 'local_qubexa_reports'),
    get_string('plus', 'local_qubexa_reports'),
    get_string('minus', 'local_qubexa_reports'),
    get_string('net', 'local_qubexa_reports'),
]);

foreach ($report->export_for_csv() as $row) {
    $csv->add_data($row);
}

$csv->download_file();
exit;
