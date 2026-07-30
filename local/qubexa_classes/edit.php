<?php
require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();

require_capability(
    'local/qubexa_classes:manage',
    $context
);

$id = optional_param(
    'id',
    0,
    PARAM_INT
);

$returnurl = new moodle_url(
    '/local/qubexa/index.php',
    ['page' => 'classes']
);

$pageurl = new moodle_url(
    '/local/qubexa_classes/edit.php'
);

if ($id) {
    $pageurl->param('id', $id);
}

$record = null;

if ($id) {
    $record = $DB->get_record(
        'local_qubexa_classes',
        [
            'id' => $id,
            'userid' => $USER->id,
        ],
        '*',
        MUST_EXIST
    );
}

$title = get_string(
    $id ? 'editclass' : 'addclass',
    'local_qubexa_classes'
);

$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('embedded');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$form = new \local_qubexa_classes\form\class_form(
    $pageurl
);

if ($record) {
    $form->set_data($record);
}

if ($form->is_cancelled()) {
    redirect($returnurl);
}

if ($data = $form->get_data()) {
    $savedrecord = new stdClass();

    $savedrecord->userid =
        (int) $USER->id;

    $savedrecord->schoolname =
        trim($data->schoolname ?? '');

    $savedrecord->classname =
        trim($data->classname);

    $savedrecord->sectionname =
        trim($data->sectionname ?? '');

    $savedrecord->coursename =
        trim($data->coursename);

    $savedrecord->academicyear =
        trim($data->academicyear);

    $savedrecord->color =
        $data->color;

    $savedrecord->status =
        (int) $data->status;

    $savedrecord->timemodified = time();
    if ($record) {
        $savedrecord->id =
            (int) $record->id;

        $DB->update_record(
            'local_qubexa_classes',
            $savedrecord
        );

        $message = get_string(
            'classupdated',
            'local_qubexa_classes'
        );
    } else {
        $savedrecord->timecreated = time();

        $DB->insert_record(
            'local_qubexa_classes',
            $savedrecord
        );

        $message = get_string(
            'classcreated',
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

echo $OUTPUT->header();

echo html_writer::start_div(
    'qubexa-class-form-page'
);

echo html_writer::link(
    $returnurl,
    '← ' . get_string(
        'classes',
        'local_qubexa_classes'
    ),
    ['class' => 'qubexa-class-form-back']
);

echo $OUTPUT->heading($title);

$form->display();

echo html_writer::end_div();

echo $OUTPUT->footer();