<?php
require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();

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
$urlparams = [];

if ($classid > 0) {
    $urlparams['reportclassid'] = $classid;
}

if ($studentquery !== '') {
    $urlparams['reportstudent'] = $studentquery;
}

if ($datefrom !== '') {
    $urlparams['reportdatefrom'] = $datefrom;
}

if ($dateto !== '') {
    $urlparams['reportdateto'] = $dateto;
}

$PAGE->set_context($context);
$PAGE->set_url(
    new moodle_url('/local/qubexa_reports/print.php', $urlparams)
);
$PAGE->set_pagelayout('print');
$PAGE->set_title(
    get_string('printtitle', 'local_qubexa_reports')
);
$PAGE->set_heading(
    get_string('printtitle', 'local_qubexa_reports')
);
$PAGE->requires->css(
    new moodle_url('/local/qubexa_reports/print.css')
);
$PAGE->requires->js(
    new moodle_url('/local/qubexa_reports/print.js')
);

$report = new \local_qubexa_reports\output\reports_page(
    (int) $USER->id,
    $classid,
    $studentquery,
    $datefrom,
    $dateto
);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template(
    'local_qubexa_reports/print_report',
    $report->export_for_print()
);
echo $OUTPUT->footer();
