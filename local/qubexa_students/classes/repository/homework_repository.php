<?php
namespace local_qubexa_students\repository;

defined('MOODLE_INTERNAL') || die();

/**
 * Student homework data access.
 *
 * @package local_qubexa_students
 */
final class homework_repository {
    public function find_for_student(
        int $studentid,
        int $userid
    ): array {
        global $DB;

        return $DB->get_records(
            'local_qubexa_homeworks',
            [
                'studentid' => $studentid,
                'userid' => $userid,
            ],
            'status DESC, duedate ASC, id DESC'
        );
    }

    public function create(\stdClass $record): int {
        global $DB;

        return (int) $DB->insert_record(
            'local_qubexa_homeworks',
            $record
        );
    }

    public function update_status_owned(
        int $homeworkid,
        int $studentid,
        int $userid,
        string $status
    ): bool {
        global $DB;

        $record = $DB->get_record(
            'local_qubexa_homeworks',
            [
                'id' => $homeworkid,
                'studentid' => $studentid,
                'userid' => $userid,
            ],
            'id',
            IGNORE_MISSING
        );

        if (!$record) {
            return false;
        }

        $record->status = $status;
        $record->timemodified = time();

        return $DB->update_record(
            'local_qubexa_homeworks',
            $record
        );
    }

    public function delete_owned(
        int $homeworkid,
        int $studentid,
        int $userid
    ): bool {
        global $DB;

        $conditions = [
            'id' => $homeworkid,
            'studentid' => $studentid,
            'userid' => $userid,
        ];

        if (!$DB->record_exists(
            'local_qubexa_homeworks',
            $conditions
        )) {
            return false;
        }

        return $DB->delete_records(
            'local_qubexa_homeworks',
            $conditions
        );
    }
}
