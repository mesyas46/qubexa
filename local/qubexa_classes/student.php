<?php
require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();

require_capability(
    'local/qubexa_classes:manage',
    $context
);

$classid = required_param(
    'classid',
    PARAM_INT
);

$id = optional_param(
    'id',
    0,
    PARAM_INT
);

$classrecord = $DB->get_record(
    'local_qubexa_classes',
    [
        'id' => $classid,
        'userid' => $USER->id,
    ],
    '*',
    MUST_EXIST
);

$studentrecord = null;

if ($id) {
    $studentrecord = $DB->get_record(
        'local_qubexa_class_students',
        [
            'id' => $id,
            'classid' => $classid,
            'userid' => $USER->id,
        ],
        '*',
        MUST_EXIST
    );
}

$returnurl = new moodle_url(
    '/local/qubexa/index.php',
    [
        'page' => 'classes',
        'classid' => $classid,
    ]
);
$pageurl = new moodle_url(
    '/local/qubexa_classes/student.php',
    ['classid' => $classid]
);

if ($id) {
    $pageurl->param('id', $id);
}

$title = get_string(
    $id ? 'editstudent' : 'addstudent',
    'local_qubexa_classes'
);

$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('embedded');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$form = new \local_qubexa_classes\form\student_form(
    $pageurl
);

if ($studentrecord) {
    $form->set_data($studentrecord);
} else {
    $form->set_data([
        'classid' => $classid,
        'status' => 1,
    ]);
}

if ($form->is_cancelled()) {
    redirect($returnurl);
}
if ($data = $form->get_data()) {
    $savedrecord = new stdClass();

    $savedrecord->userid =
        (int) $USER->id;

    $savedrecord->classid =
        (int) $classrecord->id;

    $savedrecord->firstname =
        trim($data->firstname);

    $savedrecord->lastname =
        trim($data->lastname);

    $savedrecord->studentnumber =
        trim($data->studentnumber ?? '');

    $savedrecord->status =
        (int) $data->status;

    $savedrecord->timemodified = time();

    if ($studentrecord) {
        $savedrecord->id =
            (int) $studentrecord->id;

        $DB->update_record(
            'local_qubexa_class_students',
            $savedrecord
        );

        $message = get_string(
            'studentupdated',
            'local_qubexa_classes'
        );
    } else {
        $savedrecord->timecreated = time();

        $DB->insert_record(
            'local_qubexa_class_students',
            $savedrecord
        );

        $message = get_string(
            'studentcreated',
            'local_qubexa_classes'
        );
    }

    redirect(
        $returnurl,
        $message,
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}
$PAGE->requires->css(
    new moodle_url(
        '/local/qubexa_classes/styles.css'
    )
);

$classlabel = format_string(
    $classrecord->classname
);

if (!empty($classrecord->sectionname)) {
    $classlabel .= ' / ' .
        format_string(
            $classrecord->sectionname
        );
}

echo $OUTPUT->header();

echo html_writer::start_div(
    'qubexa-class-form-page'
);

echo html_writer::link(
    $returnurl,
    '← ' . $classlabel,
    ['class' => 'qubexa-class-form-back']
);

echo $OUTPUT->heading($title);

$form->display();

echo html_writer::end_div();

echo $OUTPUT->footer();