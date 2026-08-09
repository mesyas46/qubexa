<?php
namespace local_qubexa_students\repository;

defined('MOODLE_INTERNAL') || die();

/**
 * Student lesson data access.
 *
 * @package local_qubexa_students
 */
final class lesson_repository {
    public function find_for_student(
        int $studentid,
        int $userid
    ): array {
        global $DB;

        return $DB->get_records(
            'local_qubexa_student_lessons',
            [
                'studentid' => $studentid,
                'userid' => $userid,
            ],
            'lessondate DESC, starttime DESC, id DESC'
        );
    }

    public function create(\stdClass $record): int {
        global $DB;

        return (int) $DB->insert_record(
            'local_qubexa_student_lessons',
            $record
        );
    }

    public function delete_owned(
        int $lessonid,
        int $studentid,
        int $userid
    ): bool {
        global $DB;

        $conditions = [
            'id' => $lessonid,
            'studentid' => $studentid,
            'userid' => $userid,
        ];

        if (!$DB->record_exists(
            'local_qubexa_student_lessons',
            $conditions
        )) {
            return false;
        }

        return $DB->delete_records(
            'local_qubexa_student_lessons',
            $conditions
        );
    }
}
