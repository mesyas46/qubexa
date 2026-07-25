<?php
require_once(__DIR__ . '/../../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/qubexa_students:view', $context);

$action = required_param('action', PARAM_ALPHA);

header('Content-Type: application/json; charset=utf-8');

$service = new \local_qubexa_students\service\note_service();

try {
    if ($action === 'list') {
        $studentid = required_param('studentid', PARAM_INT);

        echo json_encode([
            'success' => true,
            'notes' => $service->list_notes(
                $studentid,
                (int) $USER->id
            ),
        ]);
        exit;
    }

    require_sesskey();

    if ($action === 'add') {
        $studentid = required_param('studentid', PARAM_INT);
        $note = required_param('note', PARAM_RAW_TRIMMED);

        $service->add_note(
            $studentid,
            (int) $USER->id,
            $note
        );

        echo json_encode([
            'success' => true,
            'notes' => $service->list_notes(
                $studentid,
                (int) $USER->id
            ),
        ]);
        exit;
    }

    if ($action === 'delete') {
        $noteid = required_param('noteid', PARAM_INT);
        $studentid = required_param('studentid', PARAM_INT);

        $service->delete_note($noteid, (int) $USER->id);

        echo json_encode([
            'success' => true,
            'notes' => $service->list_notes(
                $studentid,
                (int) $USER->id
            ),
        ]);
        exit;
    }

    throw new invalid_parameter_exception('Geçersiz işlem.');
} catch (Throwable $exception) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage(),
    ]);
}
