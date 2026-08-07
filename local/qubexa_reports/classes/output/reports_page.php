<?php
namespace local_qubexa_reports\output;

defined('MOODLE_INTERNAL') || die();

final class reports_page implements \renderable, \templatable {
    private int $userid;
    private int $classid;
    private string $studentquery;
    private string $datefrom;
    private string $dateto;

    public function __construct(
        int $userid,
        int $classid = 0,
        string $studentquery = '',
        string $datefrom = '',
        string $dateto = ''
    ) {
        $this->userid = $userid;
        $this->classid = $classid;
        $this->studentquery = $this->normalise_student_query(
            $studentquery
        );
        $this->datefrom = $this->normalise_date($datefrom);
        $this->dateto = $this->normalise_date($dateto);

        if (
            $this->datefrom !== '' &&
            $this->dateto !== '' &&
            $this->datefrom > $this->dateto
        ) {
            [$this->datefrom, $this->dateto] = [
                $this->dateto,
                $this->datefrom,
            ];
        }
    }

    public function export_for_template($output): array {
        $classrecords = $this->get_classes();
        $this->normalise_classid($classrecords);

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

        $exportparams = $this->filter_params();

        $hasfilter = !empty($exportparams);

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
            'exporturl' => (new \moodle_url(
                '/local/qubexa_reports/export.php',
                $exportparams
            ))->out(false),
            'printurl' => (new \moodle_url(
                '/local/qubexa_reports/print.php',
                $exportparams
            ))->out(false),
            'exportcsvlabel' => get_string(
                'exportcsv',
                'local_qubexa_reports'
            ),
            'printreportlabel' => get_string(
                'printreport',
                'local_qubexa_reports'
            ),
            'hasfilter' => $hasfilter,
            'classoptions' => $classoptions,
            'hasclassoptions' => !empty($classoptions),
            'studentquery' => $this->studentquery,
            'datefrom' => $this->datefrom,
            'dateto' => $this->dateto,
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
            'studentfilterlabel' => get_string(
                'filterstudent',
                'local_qubexa_reports'
            ),
            'studentplaceholder' => get_string(
                'studentplaceholder',
                'local_qubexa_reports'
            ),
            'datefromlabel' => get_string(
                'datefrom',
                'local_qubexa_reports'
            ),
            'datetolabel' => get_string(
                'dateto',
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
            'actionslabel' => get_string(
                'actions',
                'local_qubexa_reports'
            ),
            'viewprogresslabel' => get_string(
                'viewprogress',
                'local_qubexa_reports'
            ),
            'emptytitle' => get_string(
                $hasfilter ? 'nofilterresults' : 'noreportdata',
                'local_qubexa_reports'
            ),
            'emptydesc' => get_string(
                $hasfilter
                    ? 'nofilterresultsdesc'
                    : 'noreportdatadesc',
                'local_qubexa_reports'
            ),
        ];
    }

    public function export_for_csv(): array {
        $classrecords = $this->get_classes();
        $this->normalise_classid($classrecords);
        $exportrows = [];

        foreach ($this->get_participation_rows() as $row) {
            $exportrows[] = [
                $this->spreadsheet_text(
                    $row['exportfullname']
                ),
                $this->spreadsheet_text(
                    $row['exportstudentnumber']
                ),
                $this->spreadsheet_text(
                    $row['exportclassname']
                ),
                $row['pluscount'],
                $row['minuscount'],
                $row['netvalue'],
            ];
        }

        return $exportrows;
    }

    public function export_for_print(): array {
        $classrecords = $this->get_classes();
        $this->normalise_classid($classrecords);
        $rows = $this->get_participation_rows();

        $classname = get_string(
            'allclasses',
            'local_qubexa_reports'
        );

        if (
            $this->classid > 0 &&
            isset($classrecords[$this->classid])
        ) {
            $classname = $this->class_name(
                $classrecords[$this->classid]
            );
        }

        $filterparams = $this->filter_params();
        $hasfilter = !empty($filterparams);

        return [
            'reporttitle' => get_string(
                'printtitle',
                'local_qubexa_reports'
            ),
            'reportdesc' => get_string(
                'printdesc',
                'local_qubexa_reports'
            ),
            'generatedlabel' => get_string(
                'generatedat',
                'local_qubexa_reports'
            ),
            'generatedat' => userdate(
                time(),
                get_string(
                    'strftimedatetimeshort',
                    'langconfig'
                )
            ),
            'filters' => $this->print_filters($classname),
            'backurl' => \local_qubexa\workspace::page_url(
                'reports',
                $filterparams
            )->out(false),
            'backlabel' => get_string(
                'backtoreports',
                'local_qubexa_reports'
            ),
            'printlabel' => get_string(
                'printnow',
                'local_qubexa_reports'
            ),
            'rows' => $rows,
            'hasrows' => !empty($rows),
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
                $hasfilter ? 'nofilterresults' : 'noreportdata',
                'local_qubexa_reports'
            ),
            'emptydesc' => get_string(
                $hasfilter
                    ? 'nofilterresultsdesc'
                    : 'noreportdatadesc',
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

        $haspointtable = $this->table_exists(
            'local_qubexa_class_points'
        );
        $pointdatesql = '';
        $pointdateparams = [];

        if ($haspointtable && $this->datefrom !== '') {
            $pointdatesql .=
                ' AND p.timecreated >= :pointdatefrom';
            $pointdateparams['pointdatefrom'] =
                $this->date_timestamp($this->datefrom);
        }

        if ($haspointtable && $this->dateto !== '') {
            $pointdatesql .=
                ' AND p.timecreated < :pointdateto';
            $pointdateparams['pointdateto'] =
                $this->date_timestamp($this->dateto, true);
        }

        $pointjoin = $haspointtable
            ? "LEFT JOIN {local_qubexa_class_points} p
                     ON p.studentid = s.id
                    AND p.classid = c.id
                    AND p.userid = :pointuserid
                    {$pointdatesql}"
            : '';

        $params = ['reportuserid' => $this->userid];

        if ($pointjoin !== '') {
            $params['pointuserid'] = $this->userid;
            $params = array_merge($params, $pointdateparams);
        }

        $classsql = '';

        if ($this->classid > 0) {
            $classsql = ' AND c.id = :filterclassid';
            $params['filterclassid'] = $this->classid;
        }

        $studentsearchsql = '';

        if ($this->studentquery !== '') {
            $fullnamefield = $DB->sql_concat(
                's.firstname',
                "' '",
                's.lastname'
            );
            $studentsearchsql = ' AND ' . $DB->sql_like(
                $fullnamefield,
                ':studentquery',
                false,
                false
            );
            $params['studentquery'] = '%' .
                $DB->sql_like_escape($this->studentquery) . '%';
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
                {$studentsearchsql}
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
            $exportclassname = trim((string) $record->classname);

            if (!empty($record->sectionname)) {
                $classname .= ' / ' .
                    format_string($record->sectionname);
                $exportclassname .= ' / ' .
                    trim((string) $record->sectionname);
            }

            $exportfullname = trim(
                $record->firstname . ' ' .
                $record->lastname
            );

            $rows[] = [
                'fullname' => format_string(
                    $exportfullname
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
                'netvalue' => $net,
                'netclass' => $this->net_tone($net),
                'exportfullname' => $exportfullname,
                'exportstudentnumber' =>
                    (string) $record->studentnumber,
                'exportclassname' => $exportclassname,
                'classurl' => \local_qubexa\workspace::page_url(
                    'classes',
                    ['classid' => (int) $record->classid]
                )->out(false),
                'detailurl' => \local_qubexa\workspace::page_url(
                    'reports',
                    array_merge(
                        $this->filter_params(),
                        ['reportstudentid' => (int) $record->id]
                    )
                )->out(false),
            ];
        }

        return $rows;
    }

    private function normalise_classid(array $classrecords): void {
        if (
            $this->classid > 0 &&
            !isset($classrecords[$this->classid])
        ) {
            $this->classid = 0;
        }
    }

    private function filter_params(): array {
        $params = [];

        if ($this->classid > 0) {
            $params['reportclassid'] = $this->classid;
        }

        if ($this->studentquery !== '') {
            $params['reportstudent'] = $this->studentquery;
        }

        if ($this->datefrom !== '') {
            $params['reportdatefrom'] = $this->datefrom;
        }

        if ($this->dateto !== '') {
            $params['reportdateto'] = $this->dateto;
        }

        return $params;
    }

    private function print_filters(string $classname): array {
        $filters = [
            [
                'label' => get_string(
                    'filterclass',
                    'local_qubexa_reports'
                ),
                'value' => $classname,
            ],
        ];

        if ($this->studentquery !== '') {
            $filters[] = [
                'label' => get_string(
                    'filterstudent',
                    'local_qubexa_reports'
                ),
                'value' => $this->studentquery,
            ];
        }

        $filters[] = [
            'label' => get_string(
                'daterange',
                'local_qubexa_reports'
            ),
            'value' => $this->print_date_range(),
        ];

        return $filters;
    }

    private function print_date_range(): string {
        if ($this->datefrom === '' && $this->dateto === '') {
            return get_string(
                'alldates',
                'local_qubexa_reports'
            );
        }

        if ($this->datefrom !== '' && $this->dateto !== '') {
            return get_string(
                'daterangeboth',
                'local_qubexa_reports',
                (object) [
                    'from' => $this->display_date($this->datefrom),
                    'to' => $this->display_date($this->dateto),
                ]
            );
        }

        if ($this->datefrom !== '') {
            return get_string(
                'daterangefrom',
                'local_qubexa_reports',
                $this->display_date($this->datefrom)
            );
        }

        return get_string(
            'daterangeto',
            'local_qubexa_reports',
            $this->display_date($this->dateto)
        );
    }

    private function display_date(string $date): string {
        return userdate(
            $this->date_timestamp($date),
            get_string('strftimedate', 'langconfig')
        );
    }

    private function normalise_student_query(string $query): string {
        $query = trim(clean_param($query, PARAM_TEXT));

        return \core_text::substr($query, 0, 100);
    }

    private function normalise_date(string $date): string {
        $date = trim($date);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date)) {
            return '';
        }

        $value = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $date,
            \core_date::get_user_timezone_object()
        );
        $errors = \DateTimeImmutable::getLastErrors();

        if (
            $value === false ||
            (is_array($errors) && (
                $errors['warning_count'] > 0 ||
                $errors['error_count'] > 0
            )) ||
            $value->format('Y-m-d') !== $date
        ) {
            return '';
        }

        return $date;
    }

    private function date_timestamp(
        string $date,
        bool $nextday = false
    ): int {
        $value = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $date,
            \core_date::get_user_timezone_object()
        );

        if ($nextday) {
            $value = $value->modify('+1 day');
        }

        return $value->getTimestamp();
    }

    private function spreadsheet_text(string $value): string {
        $value = trim(clean_param($value, PARAM_TEXT));

        if (preg_match('/^[=+\-@]/u', $value)) {
            return "'" . $value;
        }

        return $value;
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
