<?php
define('NO_DEBUG_DISPLAY', true);
define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(
    new moodle_url('/local/qubexa_students/ajax/homeworks.php')
);

require_capability(
    'local/qubexa_students:view',
    $context
);

header('Content-Type: application/json; charset=utf-8');

try {
    $action = required_param('action', PARAM_ALPHA);
    $service = new \local_qubexa_students\service\homework_service();

    if ($action === 'list') {
        $studentid = required_param('studentid', PARAM_INT);

        echo json_encode([
            'success' => true,
            'data' => $service->list_homeworks(
                $studentid,
                (int) $USER->id
            ),
        ]);
        exit;
    }

    require_capability(
        'local/qubexa_students:manage',
        $context
    );

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        throw new invalid_parameter_exception(
            'Bu işlem yalnızca POST isteğiyle yapılabilir.'
        );
    }

    require_sesskey();
    $studentid = required_param('studentid', PARAM_INT);

    if ($action === 'add') {
        $service->add_homework(
            $studentid,
            (int) $USER->id,
            required_param('title', PARAM_TEXT),
            required_param('duedate', PARAM_RAW_TRIMMED),
            optional_param('description', '', PARAM_TEXT)
        );
    } elseif ($action === 'status') {
        $service->update_status(
            required_param('homeworkid', PARAM_INT),
            $studentid,
            (int) $USER->id,
            required_param('status', PARAM_ALPHA)
        );
    } elseif ($action === 'delete') {
        $service->delete_homework(
            required_param('homeworkid', PARAM_INT),
            $studentid,
            (int) $USER->id
        );
    } else {
        throw new invalid_parameter_exception('Geçersiz işlem.');
    }

    echo json_encode([
        'success' => true,
        'data' => $service->list_homeworks(
            $studentid,
            (int) $USER->id
        ),
    ]);
} catch (Throwable $exception) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage(),
    ]);
}
