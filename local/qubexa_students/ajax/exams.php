<?php
define('NO_DEBUG_DISPLAY', true);

define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../../config.php');

require_login();

$PAGE->set_url('/local/qubexa_students/ajax/exams.php');

$context = context_system::instance();
$PAGE->set_context($context);
require_capability('local/qubexa_students:view', $context);

$action = required_param('action', PARAM_ALPHA);

header('Content-Type: application/json; charset=utf-8');

$service = new \local_qubexa_students\service\exam_service();

try {
    if ($action === 'list') {
        $studentid = required_param('studentid', PARAM_INT);

        echo json_encode([
            'success' => true,
            'data' => $service->list_exams(
                $studentid,
                (int) $USER->id
            ),
        ]);
        exit;
    }

    require_capability('local/qubexa_students:manage', $context);

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        throw new invalid_parameter_exception(
            'Bu işlem yalnızca POST isteğiyle yapılabilir.'
        );
    }

    require_sesskey();

    if ($action === 'add') {
        $studentid = required_param('studentid', PARAM_INT);
        $examname = required_param('examname', PARAM_TEXT);
        $examdate = required_param('examdate', PARAM_RAW_TRIMMED);
        $correct = required_param('correct', PARAM_INT);
        $wrong = required_param('wrong', PARAM_INT);
        $blank = required_param('blank', PARAM_INT);
        $description = optional_param(
            'description',
            '',
            PARAM_TEXT
        );

        $service->add_exam(
            $studentid,
            (int) $USER->id,
            $examname,
            $examdate,
            $correct,
            $wrong,
            $blank,
            $description
        );

        echo json_encode([
            'success' => true,
            'data' => $service->list_exams(
                $studentid,
                (int) $USER->id
            ),
        ]);
        exit;
    }

    if ($action === 'delete') {
        $examid = required_param('examid', PARAM_INT);
        $studentid = required_param('studentid', PARAM_INT);

        $service->delete_exam(
            $examid,
            $studentid,
            (int) $USER->id
        );

        echo json_encode([
            'success' => true,
            'data' => $service->list_exams(
                $studentid,
                (int) $USER->id
            ),
        ]);
        exit;
    }

    throw new \invalid_parameter_exception('Geçersiz işlem.');
} catch (\Throwable $exception) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage(),
    ]);
}
