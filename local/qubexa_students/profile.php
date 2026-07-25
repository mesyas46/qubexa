<?php
require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/qubexa_students:view', $context);

$id = required_param('id', PARAM_INT);

$student = $DB->get_record(
    'local_qubexa_students',
    [
        'id' => $id,
        'userid' => $USER->id,
    ],
    '*',
    MUST_EXIST
);

$fullname = format_string(
    trim($student->firstname . ' ' . $student->lastname)
);

$PAGE->set_url(
    new moodle_url(
        '/local/qubexa_students/profile.php',
        ['id' => $student->id]
    )
);

$PAGE->set_context($context);
$PAGE->set_pagelayout('embedded');
$PAGE->set_title($fullname);
$PAGE->set_heading($fullname);
$PAGE->add_body_class('qubexa-app');

$backurl = \local_qubexa\workspace::page_url('students');

$editurl = new moodle_url(
    '/local/qubexa_students/edit.php',
    ['id' => $student->id]
);

$initials = core_text::strtoupper(
    core_text::substr($student->firstname, 0, 1) .
    core_text::substr($student->lastname, 0, 1)
);

$isactive = $student->status === 'active';

$profilecontext = [
    'fullname' => $fullname,
    'initials' => $initials,
    'grade' => s($student->grade ?? ''),
    'groupname' => s($student->groupname ?? ''),
    'phone' => s($student->phone ?? ''),
    'parentname' => s($student->parentname ?? ''),
    'statuslabel' => $isactive
        ? get_string('active', 'local_qubexa_students')
        : get_string('passive', 'local_qubexa_students'),
    'isactive' => $isactive,
    'backurl' => $backurl->out(false),
    'editurl' => $editurl->out(false),
];

$content = $OUTPUT->render_from_template(
    'local_qubexa_students/profile',
    $profilecontext
);

$appcontext = array_merge(
    \local_qubexa\workspace::context(
        'students',
        $fullname,
        get_string('studentprofile', 'local_qubexa_students')
    ),
    [
        'content' => $content,
    ]
);

echo $OUTPUT->header();

echo $OUTPUT->render_from_template(
    'local_qubexa/workspace',
    $appcontext
);

echo $OUTPUT->footer();