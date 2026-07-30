<?php
require_once(__DIR__ . '/../../config.php');

require_login();

global $DB, $OUTPUT, $PAGE, $USER;

$context = context_system::instance();

require_capability(
    'local/qubexa_calendar:manage',
    $context
);

$id = optional_param('id', 0, PARAM_INT);
$date = optional_param('date', '', PARAM_RAW_TRIMMED);

$event = null;

if ($id > 0) {
    $event = $DB->get_record(
        'local_qubexa_cal_events',
        [
            'id' => $id,
            'userid' => $USER->id,
        ],
        '*',
        MUST_EXIST
    );
}

$pageurl = new moodle_url(
    '/local/qubexa_calendar/edit.php',
    $id > 0 ? ['id' => $id] : ['date' => $date]
);

$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('embedded');
$PAGE->add_body_class('qubexa-app');

$pagetitle = $id > 0
    ? get_string('editevent', 'local_qubexa_calendar')
    : get_string('addevent', 'local_qubexa_calendar');

$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$PAGE->requires->css(
    new moodle_url('/local/qubexa/styles.css')
);

$PAGE->requires->css(
    new moodle_url('/local/qubexa_calendar/styles.css')
);

$studentrecords = $DB->get_records(
    'local_qubexa_students',
    ['userid' => $USER->id],
    'firstname ASC, lastname ASC',
    'id, firstname, lastname'
);

$studentoptions = [
    '' => '',
];

foreach ($studentrecords as $student) {
    $studentoptions[$student->id] = format_string(
        trim(
            $student->firstname .
            ' ' .
            $student->lastname
        )
    );
}

$returnurl = new moodle_url(
    '/local/qubexa/index.php',
    [
        'page' => 'calendar',
        'viewdate' => $event
            ? $event->timestart
            : time(),
    ]
);
$form = new \local_qubexa_calendar\form\event_form(
    null,
    ['students' => $studentoptions]
);

if ($form->is_cancelled()) {
    redirect($returnurl);
}

if ($data = $form->get_data()) {
    $now = time();

    $record = $event ?: new stdClass();

    $record->userid = (int) $USER->id;

    $studentid = !empty($data->studentid)
    ? (int) $data->studentid
    : 0;

if (
    $studentid > 0 &&
    !array_key_exists(
        $studentid,
        $studentoptions
    )
) {
    throw new \invalid_parameter_exception(
        'Invalid student selection.'
    );
}

$record->studentid =
    $studentid > 0
        ? $studentid
        : null;

    if (!isset($record->groupid)) {
        $record->groupid = null;
    }

    $record->title = $data->title;
    $record->description = $data->description;
    $record->eventtype = $data->eventtype;
    $record->timestart = (int) $data->timestart;
    $record->timeend = (int) $data->timeend;
    $record->status = $data->status;
    $record->recurrence = $data->recurrence;

    $record->recurrenceuntil =
        $data->recurrence !== 'none' &&
        !empty($data->recurrenceuntil)
            ? (int) $data->recurrenceuntil
            : null;

    $record->timemodified = $now;

    if ($event) {
        $DB->update_record(
            'local_qubexa_cal_events',
            $record
        );

        $message = get_string(
            'eventupdated',
            'local_qubexa_calendar'
        );
    } else {
        $record->timecreated = $now;

        $record->id = $DB->insert_record(
            'local_qubexa_cal_events',
            $record
        );

        $message = get_string(
            'eventcreated',
            'local_qubexa_calendar'
        );
    }

    $calendarurl = new moodle_url(
        '/local/qubexa/index.php',
        [
            'page' => 'calendar',
            'viewdate' => $record->timestart,
        ]
    );

    redirect(
        $calendarurl,
        $message,
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}
if (!$form->is_submitted()) {
    if ($event) {
        $formdata = clone $event;

        $formdata->studentid =
    $formdata->studentid ?? '';

        $form->set_data($formdata);
    } else {
        $timezone =
            \core_date::get_user_timezone_object();

        $start = new DateTimeImmutable(
            'today 09:00:00',
            $timezone
        );

        if (
            preg_match(
                '/^\d{4}-\d{2}-\d{2}$/',
                $date
            )
        ) {
            $selectedstart =
                DateTimeImmutable::createFromFormat(
                    '!Y-m-d H:i:s',
                    $date . ' 09:00:00',
                    $timezone
                );

            if (
                $selectedstart &&
                $selectedstart->format('Y-m-d') === $date
            ) {
                $start = $selectedstart;
            }
        }

        $defaults = new stdClass();

        $defaults->id = 0;
        $defaults->title = '';
        $defaults->studentid = '';
        $defaults->eventtype = 'lesson';
        $defaults->timestart =
            $start->getTimestamp();
        $defaults->timeend =
            $start->modify('+1 hour')->getTimestamp();
        $defaults->status = 'planned';
        $defaults->recurrence = 'none';
        $defaults->recurrenceuntil = null;
        $defaults->description = '';

        $form->set_data($defaults);
    }
}

echo $OUTPUT->header();

echo html_writer::start_div(
    'qubexa-calendar-form-shell'
);

echo html_writer::link(
    $returnurl,
    '← ' . get_string(
        'smartcalendar',
        'local_qubexa_calendar'
    ),
    [
        'class' =>
            'qubexa-calendar-form-back',
    ]
);

echo html_writer::tag(
    'h1',
    $pagetitle,
    [
        'class' =>
            'qubexa-calendar-form-title',
    ]
);

$form->display();
if ($event) {
    $deleteurl = new moodle_url(
        '/local/qubexa_calendar/delete.php',
        ['id' => $event->id]
    );

    echo html_writer::link(
        $deleteurl,
        get_string(
            'deleteevent',
            'local_qubexa_calendar'
        ),
        [
            'class' =>
                'btn btn-danger qubexa-calendar-delete-trigger',
        ]
    );
}
echo html_writer::end_div();

echo $OUTPUT->footer();