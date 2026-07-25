<?php
namespace local_qubexa_students\service;

defined('MOODLE_INTERNAL') || die();

use local_qubexa_students\repository\student_repository;

/**
 * Student application service.
 *
 * @package local_qubexa_students
 */
final class student_service {
    /** @var student_repository */
    private student_repository $repository;

    public function __construct(?student_repository $repository = null) {
        $this->repository = $repository ?? new student_repository();
    }

    /**
     * Build student list view models.
     *
     * @param int $userid
     * @param string $search
     * @param string $status
     * @return array
     */
    public function list_students(
        int $userid,
        string $search = '',
        string $status = ''
    ): array {
        $records = $this->repository->find_for_teacher(
            $userid,
            $search,
            $status
        );

        $items = [];

        foreach ($records as $student) {
            $fullname = format_string(
                trim($student->firstname . ' ' . $student->lastname)
            );

            $initials = \core_text::strtoupper(
                \core_text::substr($student->firstname, 0, 1) .
                \core_text::substr($student->lastname, 0, 1)
            );

            $isactive = $student->status === 'active';

            $editurl = new \moodle_url(
                '/local/qubexa_students/edit.php',
                ['id' => $student->id]
            );

            $deleteurl = new \moodle_url(
                '/local/qubexa_students/delete.php',
                [
                    'id' => $student->id,
                    'sesskey' => sesskey(),
                ]
            );

            $items[] = [
                'id' => (int) $student->id,
                'fullname' => $fullname,
                'initials' => $initials,
                'grade' => s($student->grade ?? ''),
                'gradevalue' => s($student->grade ?? ''),
                'groupname' => s($student->groupname ?? ''),
                'groupvalue' => s($student->groupname ?? ''),
                'phone' => s($student->phone ?? ''),
                'phonevalue' => s($student->phone ?? ''),
                'parentname' => s($student->parentname ?? ''),
                'parentvalue' => s($student->parentname ?? ''),
                'status' => $student->status,
                'statuslabel' => $isactive
                    ? get_string('active', 'local_qubexa_students')
                    : get_string('passive', 'local_qubexa_students'),
                'isactive' => $isactive,
                'ispassive' => !$isactive,
                'editurl' => $editurl->out(false),
                'deleteurl' => $deleteurl->out(false),
                'confirmdelete' => get_string(
                    'confirmdelete',
                    'local_qubexa_students'
                ),
                'panelarialabel' => $fullname . ' öğrenci profilini aç',
            ];
        }

        return $items;
    }
}
