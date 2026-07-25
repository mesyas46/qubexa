<?php
namespace local_qubexa_students\repository;

defined('MOODLE_INTERNAL') || die();

final class note_repository {
    public function find_for_student(int $studentid, int $userid): array {
        global $DB;

        return $DB->get_records(
            'local_qubexa_student_notes',
            [
                'studentid' => $studentid,
                'userid' => $userid,
            ],
            'timecreated DESC, id DESC'
        );
    }

    public function create(int $studentid, int $userid, string $note): int {
        global $DB;

        $now = time();

        return (int) $DB->insert_record(
            'local_qubexa_student_notes',
            (object) [
                'studentid' => $studentid,
                'userid' => $userid,
                'note' => $note,
                'timecreated' => $now,
                'timemodified' => $now,
            ]
        );
    }

    public function delete_owned(int $noteid, int $userid): bool {
        global $DB;

        $note = $DB->get_record(
            'local_qubexa_student_notes',
            [
                'id' => $noteid,
                'userid' => $userid,
            ],
            'id',
            IGNORE_MISSING
        );

        if (!$note) {
            return false;
        }

        return $DB->delete_records(
            'local_qubexa_student_notes',
            [
                'id' => $noteid,
                'userid' => $userid,
            ]
        );
    }
}
