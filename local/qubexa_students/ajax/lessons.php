<?php
define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(
    new moodle_url('/local/qubexa_students/ajax/lessons.php')
);

require_capability(
    'local/qubexa_students:view',
    $context
);

header('Content-Type: application/json; charset=utf-8');

try {
    $action = required_param('action', PARAM_ALPHA);
    $service = new \local_qubexa_students\service\lesson_service();

    if ($action === 'list') {
        $studentid = required_param(
            'studentid',
            PARAM_INT
        );

        echo json_encode([
            'success' => true,
            'data' => $service->list_lessons(
                $studentid,
                (int) $USER->id
            ),
        ]);
        exit;
    }

    require_sesskey();

    if ($action === 'add') {
        $studentid = required_param(
            'studentid',
            PARAM_INT
        );
        $lessondate = required_param(
            'lessondate',
            PARAM_RAW_TRIMMED
        );
        $starttime = required_param(
            'starttime',
            PARAM_RAW_TRIMMED
        );
        $endtime = required_param(
            'endtime',
            PARAM_RAW_TRIMMED
        );
        $topic = required_param(
            'topic',
            PARAM_TEXT
        );
        $status = required_param(
            'status',
            PARAM_ALPHA
        );
        $homeworkgiven = optional_param(
            'homeworkgiven',
            0,
            PARAM_BOOL
        );
        $homeworknote = optional_param(
            'homeworknote',
            '',
            PARAM_TEXT
        );
        $teachernote = optional_param(
            'teachernote',
            '',
            PARAM_TEXT
        );
        $nextlesson = optional_param(
            'nextlesson',
            '',
            PARAM_TEXT
        );

        $service->add_lesson(
            $studentid,
            (int) $USER->id,
            $lessondate,
            $starttime,
            $endtime,
            $topic,
            $status,
            (bool) $homeworkgiven,
            $homeworknote,
            $teachernote,
            $nextlesson
        );

        echo json_encode([
            'success' => true,
            'data' => $service->list_lessons(
                $studentid,
                (int) $USER->id
            ),
        ]);
        exit;
    }

    if ($action === 'delete') {
        $lessonid = required_param(
            'lessonid',
            PARAM_INT
        );
        $studentid = required_param(
            'studentid',
            PARAM_INT
        );

        $service->delete_lesson(
            $lessonid,
            (int) $USER->id
        );

        echo json_encode([
            'success' => true,
            'data' => $service->list_lessons(
                $studentid,
                (int) $USER->id
            ),
        ]);
        exit;
    }

    throw new \invalid_parameter_exception(
        'Geçersiz işlem.'
    );
} catch (\Throwable $exception) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage(),
    ]);
}
