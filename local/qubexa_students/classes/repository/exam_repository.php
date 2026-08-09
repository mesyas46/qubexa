<?php
namespace local_qubexa_students\repository;

defined('MOODLE_INTERNAL') || die();

/**
 * Student exam data access.
 *
 * @package local_qubexa_students
 */
final class exam_repository {
    public function find_for_student(int $studentid, int $userid): array {
        global $DB;

        return $DB->get_records(
            'local_qubexa_student_exams',
            [
                'studentid' => $studentid,
                'userid' => $userid,
            ],
            'examdate DESC, id DESC'
        );
    }

    public function create(\stdClass $record): int {
        global $DB;

        return (int) $DB->insert_record(
            'local_qubexa_student_exams',
            $record
        );
    }

    public function delete_owned(
        int $examid,
        int $studentid,
        int $userid
    ): bool {
        global $DB;

        $conditions = [
            'id' => $examid,
            'studentid' => $studentid,
            'userid' => $userid,
        ];

        if (!$DB->record_exists(
            'local_qubexa_student_exams',
            $conditions
        )) {
            return false;
        }

        return $DB->delete_records(
            'local_qubexa_student_exams',
            $conditions
        );
    }
}
