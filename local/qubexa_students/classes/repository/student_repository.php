<?php
namespace local_qubexa_students\repository;

defined('MOODLE_INTERNAL') || die();

/**
 * Student data access.
 *
 * @package local_qubexa_students
 */
final class student_repository {
    /**
     * Return students owned by a teacher.
     *
     * @param int $userid
     * @param string $search
     * @param string $status
     * @return array
     */
    public function find_for_teacher(
        int $userid,
        string $search = '',
        string $status = ''
    ): array {
        global $DB;

        $params = ['userid' => $userid];
        $where = 'userid = :userid';

        if ($search !== '') {
            $like = '%' . $DB->sql_like_escape($search) . '%';

            $params['searchfirstname'] = $like;
            $params['searchlastname'] = $like;
            $params['searchgroup'] = $like;

            $where .= ' AND (' .
                $DB->sql_like('firstname', ':searchfirstname', false) .
                ' OR ' .
                $DB->sql_like('lastname', ':searchlastname', false) .
                ' OR ' .
                $DB->sql_like('groupname', ':searchgroup', false) .
            ')';
        }

        if (in_array($status, ['active', 'passive'], true)) {
            $params['status'] = $status;
            $where .= ' AND status = :status';
        }

        return $DB->get_records_select(
            'local_qubexa_students',
            $where,
            $params,
            'lastname ASC, firstname ASC'
        );
    }

    /**
     * Return a student only when owned by the teacher.
     *
     * @param int $studentid
     * @param int $userid
     * @return stdClass|null
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
            ]
        );

        return $record ?: null;
    }
}
