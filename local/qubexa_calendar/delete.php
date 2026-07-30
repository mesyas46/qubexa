<?php
require_once(__DIR__ . '/../../config.php');

require_login();

global $DB, $OUTPUT, $PAGE, $USER;

$context = context_system::instance();

require_capability(
    'local/qubexa_calendar:manage',
    $context
);

$id = required_param('id', PARAM_INT);

$confirm = optional_param(
    'confirm',
    0,
    PARAM_BOOL
);

$event = $DB->get_record(
    'local_qubexa_cal_events',
    [
        'id' => $id,
        'userid' => $USER->id,
    ],
    '*',
    MUST_EXIST
);

$deleteurl = new moodle_url(
    '/local/qubexa_calendar/delete.php',
    ['id' => $event->id]
);

$returnurl = new moodle_url(
    '/local/qubexa/index.php',
    [
        'page' => 'calendar',
        'viewdate' => $event->timestart,
    ]
);

$PAGE->set_url($deleteurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('embedded');
$PAGE->add_body_class('qubexa-app');

$PAGE->set_title(
    get_string(
        'deleteevent',
        'local_qubexa_calendar'
    )
);

$PAGE->set_heading(
    get_string(
        'deleteevent',
        'local_qubexa_calendar'
    )
);

$PAGE->requires->css(
    new moodle_url('/local/qubexa/styles.css')
);

$PAGE->requires->css(
    new moodle_url(
        '/local/qubexa_calendar/styles.css'
    )
);
if ($confirm) {
    require_sesskey();

    $DB->delete_records(
        'local_qubexa_cal_events',
        [
            'id' => $event->id,
            'userid' => $USER->id,
        ]
    );

    redirect(
        $returnurl,
        get_string(
            'eventdeleted',
            'local_qubexa_calendar'
        ),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$confirmurl = new moodle_url(
    '/local/qubexa_calendar/delete.php',
    [
        'id' => $event->id,
        'confirm' => 1,
        'sesskey' => sesskey(),
    ]
);

echo $OUTPUT->header();

echo html_writer::start_div(
    'qubexa-calendar-form-shell'
);

echo html_writer::start_div(
    'qubexa-calendar-delete-card'
);

echo html_writer::tag(
    'h1',
    get_string(
        'deleteevent',
        'local_qubexa_calendar'
    ),
    ['class' => 'qubexa-calendar-form-title']
);

echo html_writer::tag(
    'p',
    get_string(
        'confirmdelete',
        'local_qubexa_calendar'
    ),
    ['class' => 'qubexa-calendar-delete-message']
);

echo html_writer::tag(
    'strong',
    format_string($event->title),
    ['class' => 'qubexa-calendar-delete-title']
);

echo html_writer::start_div(
    'qubexa-calendar-delete-actions'
);

echo html_writer::link(
    $confirmurl,
    get_string(
        'deleteevent',
        'local_qubexa_calendar'
    ),
    ['class' => 'btn btn-danger']
);

echo html_writer::link(
    $returnurl,
    get_string(
        'cancel',
        'local_qubexa_calendar'
    ),
    ['class' => 'btn btn-secondary']
);

echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo $OUTPUT->footer();