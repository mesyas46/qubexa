<?php
require_once(__DIR__ . '/../../config.php');

require_login();
require_sesskey();

$context = context_system::instance();

require_capability(
    'local/qubexa_classes:manage',
    $context
);

$classid = required_param(
    'classid',
    PARAM_INT
);

$studentid = required_param(
    'studentid',
    PARAM_INT
);

$pointvalue = required_param(
    'value',
    PARAM_INT
);

if (!in_array(
    $pointvalue,
    [-1, 1],
    true
)) {
    throw new invalid_parameter_exception(
        'Point value must be 1 or -1.'
    );
}

$classrecord = $DB->get_record(
    'local_qubexa_classes',
    [
        'id' => $classid,
        'userid' => $USER->id,
    ],
    '*',
    MUST_EXIST
);

$studentrecord = $DB->get_record(
    'local_qubexa_class_students',
    [
        'id' => $studentid,
        'classid' => $classrecord->id,
        'userid' => $USER->id,
    ],
    '*',
    MUST_EXIST
);
$pointrecord = new stdClass();

$pointrecord->userid =
    (int) $USER->id;

$pointrecord->classid =
    (int) $classrecord->id;

$pointrecord->studentid =
    (int) $studentrecord->id;

$pointrecord->pointvalue =
    $pointvalue;

$pointrecord->timecreated = time();

$DB->insert_record(
    'local_qubexa_class_points',
    $pointrecord
);

$message = get_string(
    $pointvalue === 1
        ? 'plusadded'
        : 'minusadded',
    'local_qubexa_classes'
);

$returnurl = new moodle_url(
    '/local/qubexa/index.php',
    [
        'page' => 'classes',
        'classid' => $classrecord->id,
    ]
);

redirect(
    $returnurl,
    $message,
    null,
    \core\output\notification::NOTIFY_SUCCESS
);