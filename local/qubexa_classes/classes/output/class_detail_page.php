<?php
namespace local_qubexa_classes\output;

defined('MOODLE_INTERNAL') || die();

final class class_detail_page implements
        \renderable,
        \templatable {

    private int $classid;
    private int $userid;

    public function __construct(
        int $classid,
        int $userid
    ) {
        $this->classid = $classid;
        $this->userid = $userid;
    }

    public function export_for_template(
        $output
    ): array {
        global $DB;

        $record = $DB->get_record(
            'local_qubexa_classes',
            [
                'id' => $this->classid,
                'userid' => $this->userid,
            ],
            '*',
            MUST_EXIST
        );

        $displayname = format_string(
            $record->classname
        );

        if (!empty($record->sectionname)) {
            $displayname .= ' / ' .
                format_string(
                    $record->sectionname
                );
        }

        $colour = (string) $record->color;

        if (!preg_match(
            '/^#[0-9a-fA-F]{6}$/',
            $colour
        )) {
            $colour = '#1769c2';
        }
        $studentrecords = $DB->get_records(
            'local_qubexa_class_students',
            [
                'userid' => $this->userid,
                'classid' => $this->classid,
            ],
            'status DESC, lastname ASC, firstname ASC'
        );
                $pointstats = $DB->get_records_sql(
            "SELECT studentid,
                    SUM(
                        CASE
                            WHEN pointvalue = 1 THEN 1
                            ELSE 0
                        END
                    ) AS pluscount,
                    SUM(
                        CASE
                            WHEN pointvalue = -1 THEN 1
                            ELSE 0
                        END
                    ) AS minuscount,
                    SUM(pointvalue) AS pointtotal
               FROM {local_qubexa_class_points}
              WHERE userid = :pointuserid
                AND classid = :pointclassid
           GROUP BY studentid",
            [
                'pointuserid' => $this->userid,
                'pointclassid' => $this->classid,
            ]
        );
        $students = [];

        foreach ($studentrecords as $student) {
                        $pointstat =
                $pointstats[$student->id] ?? null;

            $pluscount = $pointstat
                ? (int) $pointstat->pluscount
                : 0;

            $minuscount = $pointstat
                ? (int) $pointstat->minuscount
                : 0;

            $pointtotal = $pointstat
                ? (int) $pointstat->pointtotal
                : 0;
            $fullname = trim(
                $student->firstname . ' ' .
                $student->lastname
            );

            $students[] = [
                'id' => (int) $student->id,
				                'classid' => (int) $this->classid,
                'fullname' => format_string(
                    $fullname
                ),
                'initial' => \core_text::strtoupper(
                    \core_text::substr(
                        $student->firstname,
                        0,
                        1
                    )
                ),
                'studentnumber' => s(
                    (string) $student->studentnumber
                ),
                'hasnumber' => !empty(
                    $student->studentnumber
                ),
                'statuslabel' => get_string(
                    $student->status
                        ? 'active'
                        : 'inactive',
                    'local_qubexa_classes'
                ),
                'pluscount' => $pluscount,
                'minuscount' => $minuscount,
                'pointtotal' => $pointtotal,
                'ispositive' => $pointtotal > 0,
                'isnegative' => $pointtotal < 0,

                'plusurl' => (
                    new \moodle_url(
                        '/local/qubexa_classes/point.php',
                        [
                            'classid' => $this->classid,
                            'studentid' => $student->id,
                            'value' => 1,
                            'sesskey' => sesskey(),
                        ]
                    )
                )->out(false),

                'minusurl' => (
                    new \moodle_url(
                        '/local/qubexa_classes/point.php',
                        [
                            'classid' => $this->classid,
                            'studentid' => $student->id,
                            'value' => -1,
                            'sesskey' => sesskey(),
                        ]
                    )
                )->out(false),
                'editurl' => (
                    new \moodle_url(
                        '/local/qubexa_classes/student.php',
                        [
                            'classid' => $this->classid,
                            'id' => $student->id,
                        ]
                    )
                )->out(false),
            ];
        }
        return [
            'classid' => (int) $record->id,
            'students' => $students,
            'hasstudents' => !empty($students),
            'studentcount' => count($students),
            'displayname' => $displayname,

            'schoolname' => format_string(
                (string) $record->schoolname
            ),
            'hasschool' => !empty(
                $record->schoolname
            ),

            'coursename' => format_string(
                $record->coursename
            ),
            'academicyear' => s(
                $record->academicyear
            ),
            'colour' => $colour,

            'statuslabel' => get_string(
                $record->status
                    ? 'active'
                    : 'inactive',
                'local_qubexa_classes'
            ),

            'backurl' => (
                new \moodle_url(
                    '/local/qubexa/index.php',
                    ['page' => 'classes']
                )
            )->out(false),

            'editurl' => (
                new \moodle_url(
                    '/local/qubexa_classes/edit.php',
                    ['id' => $record->id]
                )
            )->out(false),

            'addstudenturl' => (
                new \moodle_url(
                    '/local/qubexa_classes/student.php',
                    ['classid' => $record->id]
                )
            )->out(false),
        ];
    }
}