<?php
namespace local_qubexa_students\output;

defined('MOODLE_INTERNAL') || die();

use local_qubexa_students\service\student_service;

final class students_page implements \renderable, \templatable {
    private string $search;
    private string $status;

    public function __construct(string $search, string $status) {
        $this->search = $search;
        $this->status = $status;
    }

    public function export_for_template($output): array {
        global $USER;

        $service = new student_service();

        $students = $service->list_students(
            (int) $USER->id,
            $this->search,
            $this->status
        );

        return [
            'studentsintro' => get_string(
                'studentsintro',
                'local_qubexa_students'
            ),
            'addstudentlabel' => get_string(
                'addstudent',
                'local_qubexa_students'
            ),
            'addstudenturl' => (
                new \moodle_url('/local/qubexa_students/edit.php')
            )->out(false),
            'filteraction' => \local_qubexa\workspace::page_url(
                'students'
            )->out(false),
            'search' => $this->search,
            'searchplaceholder' => get_string(
                'searchplaceholder',
                'local_qubexa_students'
            ),
            'filterlabel' => get_string(
                'filter',
                'local_qubexa_students'
            ),
            'allstatuseslabel' => get_string(
                'allstatuses',
                'local_qubexa_students'
            ),
            'activelabel' => get_string(
                'active',
                'local_qubexa_students'
            ),
            'passivelabel' => get_string(
                'passive',
                'local_qubexa_students'
            ),
            'statusallselected' => $this->status === '',
            'statusactiveselected' => $this->status === 'active',
            'statuspassiveselected' => $this->status === 'passive',
            'studentcount' => get_string(
                'studentcount',
                'local_qubexa_students',
                count($students)
            ),
            'hasstudents' => !empty($students),
            'students' => array_values($students),
            'nostudents' => get_string(
                'nostudents',
                'local_qubexa_students'
            ),
            'nostudentsdesc' => get_string(
                'nostudentsdesc',
                'local_qubexa_students'
            ),
            'addfirststudentlabel' => get_string(
                'addfirststudent',
                'local_qubexa_students'
            ),
            'studentlabel' => get_string(
                'student',
                'local_qubexa_students'
            ),
            'gradelabel' => get_string(
                'grade',
                'local_qubexa_students'
            ),
            'groupnamelabel' => get_string(
                'groupname',
                'local_qubexa_students'
            ),
            'phonelabel' => get_string(
                'phone',
                'local_qubexa_students'
            ),
            'parentlabel' => get_string(
                'parent',
                'local_qubexa_students'
            ),
            'statuslabel' => get_string(
                'status',
                'local_qubexa_students'
            ),
            'actionslabel' => get_string(
                'actions',
                'local_qubexa_students'
            ),
            'editlabel' => get_string('edit'),
            'deletelabel' => get_string('delete'),
            'notesendpoint' => (
                new \moodle_url(
                    '/local/qubexa_students/ajax/notes.php'
                )
            )->out(false),
            'examsendpoint' => (
                new \moodle_url(
                    '/local/qubexa_students/ajax/exams.php'
                )
            )->out(false),
            'lessonsendpoint' => (
                new \moodle_url(
                    '/local/qubexa_students/ajax/lessons.php'
                )
            )->out(false),
            'timelineendpoint' => (
                new \moodle_url(
                    '/local/qubexa_students/ajax/timeline.php'
                )
            )->out(false),
            'paymentsendpoint' => (
                new \moodle_url(
                    '/local/qubexa_students/ajax/payments.php'
                )
            )->out(false),
            'homeworksendpoint' => (
                new \moodle_url(
                    '/local/qubexa_students/ajax/homeworks.php'
                )
            )->out(false),
            'paneltabs' => student_panel_registry::tabs(),
            'paneltabcount' => count(student_panel_registry::tabs()),
            'sesskey' => sesskey(),
        ];
    }
}
