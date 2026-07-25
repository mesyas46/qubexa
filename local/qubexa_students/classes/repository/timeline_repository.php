<?php
namespace local_qubexa_students\repository;

defined('MOODLE_INTERNAL') || die();

/**
 * Collects timeline records from existing student modules.
 *
 * @package local_qubexa_students
 */
final class timeline_repository {
    /**
     * Return the student when it belongs to the teacher.
     *
     * @param int $studentid
     * @param int $userid
     * @return \stdClass|null
     */
    public function find_owned_student(
        int $studentid,
        int $userid
    ): ?\stdClass {
        global $DB;

        $record = $DB->get_record(
            'local_qubexa_students',
            [
                'id' => $studentid,
                'userid' => $userid,
            ],
            '*',
            IGNORE_MISSING
        );

        return $record ?: null;
    }

    /**
     * Return teacher notes.
     *
     * @param int $studentid
     * @param int $userid
     * @return array
     */
    public function find_notes(
        int $studentid,
        int $userid
    ): array {
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

    /**
     * Return exam results.
     *
     * @param int $studentid
     * @param int $userid
     * @return array
     */
    public function find_exams(
        int $studentid,
        int $userid
    ): array {
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

    /**
     * Return lesson records.
     *
     * @param int $studentid
     * @param int $userid
     * @return array
     */
    public function find_lessons(
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
}
