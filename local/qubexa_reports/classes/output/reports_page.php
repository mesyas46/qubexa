<?php
namespace local_qubexa_reports\output;

defined('MOODLE_INTERNAL') || die();

final class reports_page implements \renderable, \templatable {
    private int $userid;
    private int $classid;

    public function __construct(int $userid, int $classid = 0) {
        $this->userid = $userid;
        $this->classid = $classid;
    }

    public function export_for_template($output): array {
        $classrecords = $this->get_classes();

        if (
            $this->classid > 0 &&
            !isset($classrecords[$this->classid])
        ) {
            $this->classid = 0;
        }

        $summary = $this->get_summary();
        $rows = $this->get_participation_rows();
        $classoptions = [];

        foreach ($classrecords as $classrecord) {
            $classoptions[] = [
                'id' => (int) $classrecord->id,
                'label' => $this->class_name($classrecord),
                'selected' =>
                    (int) $classrecord->id === $this->classid,
            ];
        }

        return [
            'overviewtitle' => get_string(
                'overviewtitle',
                'local_qubexa_reports'
            ),
            'overviewdesc' => get_string(
                'overviewdesc',
                'local_qubexa_reports'
            ),
            'metrics' => [
                $this->metric(
                    'activeclasses',
                    $summary['activeclasses'],
                    'is-blue'
                ),
                $this->metric(
                    'classstudents',
                    $summary['classstudents'],
                    'is-violet'
                ),
                $this->metric(
                    'privatestudents',
                    $summary['privatestudents'],
                    'is-cyan'
                ),
                $this->metric(
                    'monthlylessons',
                    $summary['monthlylessons'],
                    'is-orange'
                ),
                $this->metric(
                    'attendance',
                    $summary['attendance'] . '%',
                    'is-green'
                ),
                $this->metric(
                    'examaverage',
                    format_float($summary['examaverage'], 1),
                    'is-indigo'
                ),
                $this->metric(
                    'monthlycollection',
                    format_float(
                        $summary['monthlycollection'],
                        2
                    ) . ' ₺',
                    'is-emerald'
                ),
                $this->metric(
                    'participationnet',
                    $this->signed_number(
                        $summary['participationnet']
                    ),
                    $this->net_tone(
                        $summary['participationnet']
                    )
                ),
            ],
            'participationtitle' => get_string(
                'participationtitle',
                'local_qubexa_reports'
            ),
            'participationdesc' => get_string(
                'participationdesc',
                'local_qubexa_reports'
            ),
            'filterurl' => \local_qubexa\workspace::page_url(
                'reports'
            )->out(false),
            'reseturl' => \local_qubexa\workspace::page_url(
                'reports'
            )->out(false),
            'hasfilter' => $this->classid > 0,
            'classoptions' => $classoptions,
            'hasclassoptions' => !empty($classoptions),
            'rows' => $rows,
            'hasrows' => !empty($rows),
            'allclasseslabel' => get_string(
                'allclasses',
                'local_qubexa_reports'
            ),
            'classfilterlabel' => get_string(
                'filterclass',
                'local_qubexa_reports'
            ),
            'applyfilterlabel' => get_string(
                'applyfilter',
                'local_qubexa_reports'
            ),
            'clearfilterlabel' => get_string(
                'clearfilter',
                'local_qubexa_reports'
            ),
            'studentlabel' => get_string(
                'student',
                'local_qubexa_reports'
            ),
            'studentnumberlabel' => get_string(
                'studentnumber',
                'local_qubexa_reports'
            ),
            'classlabel' => get_string(
                'class',
                'local_qubexa_reports'
            ),
            'pluslabel' => get_string(
                'plus',
                'local_qubexa_reports'
            ),
            'minuslabel' => get_string(
                'minus',
                'local_qubexa_reports'
            ),
            'netlabel' => get_string(
                'net',
                'local_qubexa_reports'
            ),
            'emptytitle' => get_string(
                'noreportdata',
                'local_qubexa_reports'
            ),
            'emptydesc' => get_string(
                'noreportdatadesc',
                'local_qubexa_reports'
            ),
        ];
    }

    private function get_classes(): array {
        global $DB;

        if (!$this->table_exists('local_qubexa_classes')) {
            return [];
        }

        return $DB->get_records(
            'local_qubexa_classes',
            [
                'userid' => $this->userid,
                'status' => 1,
            ],
            'classname ASC, sectionname ASC'
        );
    }

    private function get_summary(): array {
        global $DB;

        $summary = [
            'activeclasses' => 0,
            'classstudents' => 0,
            'privatestudents' => 0,
            'monthlylessons' => 0,
            'attendance' => 0,
            'examaverage' => 0.0,
            'monthlycollection' => 0.0,
            'participationnet' => 0,
        ];

        if ($this->table_exists('local_qubexa_classes')) {
            $summary['activeclasses'] = $DB->count_records(
                'local_qubexa_classes',
                [
                    'userid' => $this->userid,
                    'status' => 1,
                ]
            );
        }

        if ($this->table_exists('local_qubexa_class_students')) {
            $summary['classstudents'] = $DB->count_records(
                'local_qubexa_class_students',
                [
                    'userid' => $this->userid,
                    'status' => 1,
                ]
            );
        }

        if ($this->table_exists('local_qubexa_students')) {
            $summary['privatestudents'] = $DB->count_records(
                'local_qubexa_students',
                [
                    'userid' => $this->userid,
                    'status' => 'active',
                ]
            );
        }

        if ($this->table_exists('local_qubexa_class_points')) {
            $summary['participationnet'] = (int) $DB->get_field_sql(
                "SELECT COALESCE(SUM(pointvalue), 0)
                   FROM {local_qubexa_class_points}
                  WHERE userid = :pointuserid",
                ['pointuserid' => $this->userid]
            );
        }

        $timezone = \core_date::get_user_timezone_object();
        $monthstart = new \DateTimeImmutable(
            'first day of this month 00:00:00',
            $timezone
        );
        $monthend = $monthstart->modify('+1 month');
        $dateparams = [
            'summaryuserid' => $this->userid,
            'monthstart' => $monthstart->getTimestamp(),
            'monthend' => $monthend->getTimestamp(),
        ];

        if ($this->table_exists('local_qubexa_student_lessons')) {
            $lessonstats = $DB->get_record_sql(
                "SELECT COUNT(id) AS lessoncount,
                        SUM(CASE WHEN status = 'attended'
                            THEN 1 ELSE 0 END) AS attendedcount,
                        SUM(CASE WHEN status = 'absent'
                            THEN 1 ELSE 0 END) AS absentcount
                   FROM {local_qubexa_student_lessons}
                  WHERE userid = :summaryuserid
                    AND lessondate >= :monthstart
                    AND lessondate < :monthend",
                $dateparams
            );

            $summary['monthlylessons'] =
                (int) ($lessonstats->lessoncount ?? 0);
            $attended =
                (int) ($lessonstats->attendedcount ?? 0);
            $absent =
                (int) ($lessonstats->absentcount ?? 0);
            $attendancebase = $attended + $absent;
            $summary['attendance'] = $attendancebase > 0
                ? (int) round(($attended / $attendancebase) * 100)
                : 0;
        }

        if ($this->table_exists('local_qubexa_student_exams')) {
            $summary['examaverage'] = (float) $DB->get_field_sql(
                "SELECT COALESCE(AVG(net), 0)
                   FROM {local_qubexa_student_exams}
                  WHERE userid = :examuserid",
                ['examuserid' => $this->userid]
            );
        }

        if ($this->table_exists('local_qubexa_student_payments')) {
            $summary['monthlycollection'] = (float) $DB->get_field_sql(
                "SELECT COALESCE(SUM(amount), 0)
                   FROM {local_qubexa_student_payments}
                  WHERE userid = :summaryuserid
                    AND paymentdate >= :monthstart
                    AND paymentdate < :monthend
                    AND status = :paidstatus",
                array_merge(
                    $dateparams,
                    ['paidstatus' => 'paid']
                )
            );
        }

        return $summary;
    }

    private function get_participation_rows(): array {
        global $DB;

        if (
            !$this->table_exists('local_qubexa_classes') ||
            !$this->table_exists('local_qubexa_class_students')
        ) {
            return [];
        }

        $pointselect = $this->table_exists(
            'local_qubexa_class_points'
        )
            ? "COALESCE(SUM(CASE WHEN p.pointvalue = 1
                    THEN 1 ELSE 0 END), 0) AS pluscount,
               COALESCE(SUM(CASE WHEN p.pointvalue = -1
                    THEN 1 ELSE 0 END), 0) AS minuscount,
               COALESCE(SUM(p.pointvalue), 0) AS pointtotal"
            : "0 AS pluscount,
               0 AS minuscount,
               0 AS pointtotal";

        $pointjoin = $this->table_exists(
            'local_qubexa_class_points'
        )
            ? "LEFT JOIN {local_qubexa_class_points} p
                     ON p.studentid = s.id
                    AND p.classid = c.id
                    AND p.userid = :pointuserid"
            : '';

        $params = ['reportuserid' => $this->userid];

        if ($pointjoin !== '') {
            $params['pointuserid'] = $this->userid;
        }

        $classsql = '';

        if ($this->classid > 0) {
            $classsql = ' AND c.id = :filterclassid';
            $params['filterclassid'] = $this->classid;
        }

        $records = $DB->get_records_sql(
            "SELECT s.id,
                    s.firstname,
                    s.lastname,
                    s.studentnumber,
                    c.id AS classid,
                    c.classname,
                    c.sectionname,
                    {$pointselect}
               FROM {local_qubexa_class_students} s
               JOIN {local_qubexa_classes} c
                 ON c.id = s.classid
                AND c.userid = s.userid
               {$pointjoin}
              WHERE s.userid = :reportuserid
                AND s.status = 1
                AND c.status = 1
                {$classsql}
           GROUP BY s.id,
                    s.firstname,
                    s.lastname,
                    s.studentnumber,
                    c.id,
                    c.classname,
                    c.sectionname
           ORDER BY c.classname ASC,
                    c.sectionname ASC,
                    s.lastname ASC,
                    s.firstname ASC",
            $params
        );

        $rows = [];

        foreach ($records as $record) {
            $net = (int) $record->pointtotal;
            $classname = format_string($record->classname);

            if (!empty($record->sectionname)) {
                $classname .= ' / ' .
                    format_string($record->sectionname);
            }

            $rows[] = [
                'fullname' => format_string(
                    trim(
                        $record->firstname . ' ' .
                        $record->lastname
                    )
                ),
                'studentnumber' => s(
                    (string) $record->studentnumber
                ),
                'hasstudentnumber' =>
                    !empty($record->studentnumber),
                'classname' => $classname,
                'pluscount' => (int) $record->pluscount,
                'minuscount' => (int) $record->minuscount,
                'net' => $this->signed_number($net),
                'netclass' => $this->net_tone($net),
                'classurl' => \local_qubexa\workspace::page_url(
                    'classes',
                    ['classid' => (int) $record->classid]
                )->out(false),
            ];
        }

        return $rows;
    }

    private function metric(
        string $stringkey,
        $value,
        string $tone
    ): array {
        return [
            'label' => get_string(
                $stringkey,
                'local_qubexa_reports'
            ),
            'value' => (string) $value,
            'tone' => $tone,
        ];
    }

    private function signed_number(int $number): string {
        return $number > 0
            ? '+' . $number
            : (string) $number;
    }

    private function net_tone(int $number): string {
        if ($number > 0) {
            return 'is-positive';
        }

        if ($number < 0) {
            return 'is-negative';
        }

        return 'is-neutral';
    }

    private function class_name(\stdClass $record): string {
        $name = format_string($record->classname);

        if (!empty($record->sectionname)) {
            $name .= ' / ' . format_string($record->sectionname);
        }

        return $name;
    }

    private function table_exists(string $tablename): bool {
        global $DB;

        return $DB->get_manager()->table_exists(
            new \xmldb_table($tablename)
        );
    }
}
