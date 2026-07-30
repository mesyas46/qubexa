<?php
namespace local_qubexa_classes\output;

defined('MOODLE_INTERNAL') || die();

final class classes_page implements \renderable, \templatable {
    private int $userid;

    public function __construct(int $userid) {
        $this->userid = $userid;
    }

    public function export_for_template($output): array {
        global $DB;

        $records = $DB->get_records(
            'local_qubexa_classes',
            ['userid' => $this->userid],
            'status DESC, classname ASC, sectionname ASC'
        );

        $classes = [];

        foreach ($records as $record) {
            $colour = (string) $record->color;

            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $colour)) {
                $colour = '#1769c2';
            }

            $displayname = format_string($record->classname);

            if (!empty($record->sectionname)) {
                $displayname .= ' / ' .
                    format_string($record->sectionname);
            }
            $studentcount = $DB->count_records(
                'local_qubexa_class_students',
                [
                    'userid' => $this->userid,
                    'classid' => $record->id,
                    'status' => 1,
                ]
            );
            $classes[] = [
                'id' => (int) $record->id,
                'displayname' => $displayname,
                'schoolname' => format_string(
                    (string) $record->schoolname
                ),
                'hasschool' => !empty($record->schoolname),
                'coursename' => format_string(
                    $record->coursename
                ),
                'academicyear' => s($record->academicyear),
                'studentcount' => $studentcount,
                'colour' => $colour,
                'isactive' => (bool) $record->status,
                'statuslabel' => get_string(
                    $record->status ? 'active' : 'inactive',
                    'local_qubexa_classes'
                ),
                                'viewurl' => (
                    new \moodle_url(
                        '/local/qubexa/index.php',
                        [
                            'page' => 'classes',
                            'classid' => $record->id,
                        ]
                    )
                )->out(false),
            ];
        }

        return [
            'classes' => $classes,
            'hasclasses' => !empty($classes),

            'addclassurl' => (
                new \moodle_url('/local/qubexa_classes/edit.php')
            )->out(false),

            'addclasslabel' => get_string(
                'addclass',
                'local_qubexa_classes'
            ),
            'studentcountlabel' => get_string(
                'studentcount',
                'local_qubexa_classes'
            ),
            'editclasslabel' => get_string(
                'editclass',
                'local_qubexa_classes'
            ),
            'noclasseslabel' => get_string(
                'noclasses',
                'local_qubexa_classes'
            ),
            'noclassesdesc' => get_string(
                'noclassesdesc',
                'local_qubexa_classes'
            ),
        ];
    }
}