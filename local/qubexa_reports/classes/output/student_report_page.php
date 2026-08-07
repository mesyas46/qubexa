<?php
namespace local_qubexa_reports\output;

defined('MOODLE_INTERNAL') || die();

final class student_report_page implements \renderable, \templatable {
    private int $userid;
    private int $studentid;
    private int $returnclassid;
    private string $returnstudentquery;
    private string $datefrom;
    private string $dateto;

    public function __construct(
        int $userid,
        int $studentid,
        int $returnclassid = 0,
        string $returnstudentquery = '',
        string $datefrom = '',
        string $dateto = ''
    ) {
        $this->userid = $userid;
        $this->studentid = $studentid;
        $this->returnclassid = max(0, $returnclassid);
        $this->returnstudentquery = $this->normalise_student_query(
            $returnstudentquery
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
        $student = $this->get_student();
        $progress = $this->get_progress($student);
        $fullname = trim(
            $student->firstname . ' ' . $student->lastname
        );
        $classname = $this->class_name($student);
        $returnparams = $this->return_filter_params();
        $detailparams = array_merge(
            $returnparams,
            ['reportstudentid' => $this->studentid]
        );
        $resetparams = $detailparams;
        unset(
            $resetparams['reportdatefrom'],
            $resetparams['reportdateto']
        );

        return [
            'title' => get_string(
                'studentprogress',
                'local_qubexa_reports'
            ),
            'description' => get_string(
                'studentprogressdesc',
                'local_qubexa_reports'
            ),
            'fullname' => format_string($fullname),
            'classname' => $classname,
            'studentnumber' => s(
                (string) $student->studentnumber
            ),
            'hasstudentnumber' => !empty(
                $student->studentnumber
            ),
            'studentnumberlabel' => get_string(
                'studentnumber',
                'local_qubexa_reports'
            ),
            'classurl' => \local_qubexa\workspace::page_url(
                'classes',
                ['classid' => (int) $student->classid]
            )->out(false),
            'backurl' => \local_qubexa\workspace::page_url(
                'reports',
                $returnparams
            )->out(false),
            'backlabel' => get_string(
                'backtoreportlist',
                'local_qubexa_reports'
            ),
            'filterurl' => \local_qubexa\workspace::page_url(
                'reports'
            )->out(false),
            'reseturl' => \local_qubexa\workspace::page_url(
                'reports',
                $resetparams
            )->out(false),
            'studentid' => $this->studentid,
            'returnclassid' => $this->returnclassid,
            'hasreturnclassid' => $this->returnclassid > 0,
            'returnstudentquery' => $this->returnstudentquery,
            'hasreturnstudentquery' =>
                $this->returnstudentquery !== '',
            'datefrom' => $this->datefrom,
            'dateto' => $this->dateto,
            'hasdatefilter' =>
                $this->datefrom !== '' || $this->dateto !== '',
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
            'periodsummarylabel' => get_string(
                'periodsummary',
                'local_qubexa_reports'
            ),
            'daterange' => $this->date_range_label(),
            'metrics' => [
                $this->metric(
                    'plus',
                    '+' . $progress['pluscount'],
                    'is-positive'
                ),
                $this->metric(
                    'minus',
                    $progress['minuscount'] > 0
                        ? '−' . $progress['minuscount']
                        : '0',
                    'is-negative'
                ),
                $this->metric(
                    'net',
                    $this->signed_number($progress['net']),
                    $this->net_tone($progress['net'])
                ),
                $this->metric(
                    'recordeddays',
                    $progress['recordeddays'],
                    'is-blue'
                ),
            ],
            'dailytitle' => get_string(
                'dailyprogress',
                'local_qubexa_reports'
            ),
            'dailydesc' => get_string(
                'dailyprogressdesc',
                'local_qubexa_reports'
            ),
            'datelabel' => get_string(
                'date',
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
            'rows' => $progress['rows'],
            'hasrows' => !empty($progress['rows']),
            'emptytitle' => get_string(
                'noprogressdata',
                'local_qubexa_reports'
            ),
            'emptydesc' => get_string(
                'noprogressdatadesc',
                'local_qubexa_reports'
            ),
        ];
    }

    private function get_student(): \stdClass {
        global $DB;

        if (
            !$this->table_exists('local_qubexa_classes') ||
            !$this->table_exists('local_qubexa_class_students')
        ) {
            throw new \moodle_exception(
                'studentnotfound',
                'local_qubexa_reports'
            );
        }

        $student = $DB->get_record_sql(
            "SELECT s.id,
                    s.firstname,
                    s.lastname,
                    s.studentnumber,
                    c.id AS classid,
                    c.classname,
                    c.sectionname
               FROM {local_qubexa_class_students} s
               JOIN {local_qubexa_classes} c
                 ON c.id = s.classid
                AND c.userid = s.userid
              WHERE s.id = :studentid
                AND s.userid = :studentuserid
                AND s.status = 1
                AND c.status = 1",
            [
                'studentid' => $this->studentid,
                'studentuserid' => $this->userid,
            ],
            IGNORE_MISSING
        );

        if (!$student) {
            throw new \moodle_exception(
                'studentnotfound',
                'local_qubexa_reports'
            );
        }

        return $student;
    }

    private function get_progress(\stdClass $student): array {
        global $DB;

        $result = [
            'pluscount' => 0,
            'minuscount' => 0,
            'net' => 0,
            'recordeddays' => 0,
            'rows' => [],
        ];

        if (!$this->table_exists('local_qubexa_class_points')) {
            return $result;
        }

        $select = 'userid = :pointuserid
                   AND classid = :pointclassid
                   AND studentid = :pointstudentid';
        $params = [
            'pointuserid' => $this->userid,
            'pointclassid' => (int) $student->classid,
            'pointstudentid' => $this->studentid,
        ];

        if ($this->datefrom !== '') {
            $select .= ' AND timecreated >= :pointdatefrom';
            $params['pointdatefrom'] = $this->date_timestamp(
                $this->datefrom
            );
        }

        if ($this->dateto !== '') {
            $select .= ' AND timecreated < :pointdateto';
            $params['pointdateto'] = $this->date_timestamp(
                $this->dateto,
                true
            );
        }

        $records = $DB->get_records_select(
            'local_qubexa_class_points',
            $select,
            $params,
            'timecreated DESC, id DESC',
            'id, pointvalue, timecreated'
        );
        $days = [];
        $timezone = \core_date::get_user_timezone_object();

        foreach ($records as $record) {
            $date = (new \DateTimeImmutable(
                '@' . (int) $record->timecreated
            ))->setTimezone($timezone);
            $datekey = $date->format('Y-m-d');

            if (!isset($days[$datekey])) {
                $days[$datekey] = [
                    'date' => userdate(
                        (int) $record->timecreated,
                        get_string('strftimedate', 'langconfig')
                    ),
                    'pluscount' => 0,
                    'minuscount' => 0,
                ];
            }

            if ((int) $record->pointvalue === 1) {
                $days[$datekey]['pluscount']++;
                $result['pluscount']++;
            } elseif ((int) $record->pointvalue === -1) {
                $days[$datekey]['minuscount']++;
                $result['minuscount']++;
            }
        }

        foreach ($days as $day) {
            $net = $day['pluscount'] - $day['minuscount'];
            $result['rows'][] = [
                'date' => $day['date'],
                'pluscount' => $day['pluscount'],
                'minuscount' => $day['minuscount'],
                'net' => $this->signed_number($net),
                'netclass' => $this->net_tone($net),
            ];
        }

        $result['net'] =
            $result['pluscount'] - $result['minuscount'];
        $result['recordeddays'] = count($result['rows']);

        return $result;
    }

    private function return_filter_params(): array {
        $params = [];

        if ($this->returnclassid > 0) {
            $params['reportclassid'] = $this->returnclassid;
        }

        if ($this->returnstudentquery !== '') {
            $params['reportstudent'] = $this->returnstudentquery;
        }

        if ($this->datefrom !== '') {
            $params['reportdatefrom'] = $this->datefrom;
        }

        if ($this->dateto !== '') {
            $params['reportdateto'] = $this->dateto;
        }

        return $params;
    }

    private function date_range_label(): string {
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
