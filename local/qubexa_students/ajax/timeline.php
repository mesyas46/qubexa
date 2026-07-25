<?php
define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(
    new moodle_url(
        '/local/qubexa_students/ajax/timeline.php'
    )
);

require_capability(
    'local/qubexa_students:view',
    $context
);

header('Content-Type: application/json; charset=utf-8');

try {
    $studentid = required_param(
        'studentid',
        PARAM_INT
    );
    $filter = optional_param(
        'filter',
        'all',
        PARAM_ALPHA
    );
    $limit = optional_param(
        'limit',
        100,
        PARAM_INT
    );

    $service =
        new \local_qubexa_students\service\timeline_service();

    echo json_encode([
        'success' => true,
        'data' => $service->build(
            $studentid,
            (int) $USER->id,
            $filter,
            $limit
        ),
    ]);
} catch (\Throwable $exception) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage(),
    ]);
}
