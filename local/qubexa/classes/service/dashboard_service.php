<?php
namespace local_qubexa\service;

defined('MOODLE_INTERNAL') || die();

final class dashboard_service {
    private \moodle_database $db;
    private \database_manager $dbman;

    public function __construct() {
        global $DB;
        $this->db = $DB;
        $this->dbman = $DB->get_manager();
    }

    public function build(int $userid): array {
        $students = $this->student_summary($userid);
        $payments = $this->payment_summary($userid);
        $exams = $this->exam_summary($userid);
        $lessons = $this->lesson_summary($userid);
        $activity = $this->recent_activity($userid);

        return [
            'totalstudents' => $students['total'],
            'activestudents' => $students['active'],
            'monthlycollection' => format_float($payments['monthly'], 2),
            'pendingpayments' => $payments['pending'],
            'examaverage' => format_float($exams['average'], 1),
            'todaylessons' => $lessons['count'],
            'hastodaylessons' => !empty($lessons['items']),
            'todaylessonitems' => $lessons['items'],
            'hasrecentactivity' => !empty($activity),
            'recentactivity' => $activity,
        ];
    }

    private function student_summary(int $userid): array {
        $table = 'local_qubexa_students';
        if (!$this->table_exists($table)) {
            return ['total' => 0, 'active' => 0];
        }

        $conditions = $this->owner_conditions($table, $userid);
        $total = $this->db->count_records($table, $conditions);
        $active = $total;

        if ($this->field_exists($table, 'status')) {
            $conditions['status'] = 'active';
            $active = $this->db->count_records($table, $conditions);
        } else if ($this->field_exists($table, 'active')) {
            $conditions['active'] = 1;
            $active = $this->db->count_records($table, $conditions);
        }

        return ['total' => $total, 'active' => $active];
    }

    private function payment_summary(int $userid): array {
        $table = 'local_qubexa_student_payments';
        if (!$this->table_exists($table)) {
            return ['monthly' => 0.0, 'pending' => 0];
        }

        $amountfield = $this->first_field($table, [
            'amount', 'paidamount', 'paymentamount', 'totalamount', 'total'
        ]);
        $datefield = $this->first_field($table, [
            'paymentdate', 'paiddate', 'duedate', 'timecreated'
        ]);
        $statusfield = $this->first_field($table, [
            'status', 'paymentstatus'
        ]);

        $owner = $this->owner_sql($table, $userid, 'p');
        $monthly = 0.0;

        if ($amountfield && $datefield) {
            $year = (int) userdate(time(), '%Y');
            $month = (int) userdate(time(), '%m');
            $start = make_timestamp($year, $month, 1, 0, 0, 0);
            $end = strtotime('+1 month', $start) - 1;

            $statussql = '';
            if ($statusfield) {
                $statussql = " AND LOWER(p.{$statusfield}) IN
                    ('paid','completed','received','odendi','ödendi')";
            }

            $sql = "SELECT COALESCE(SUM(p.{$amountfield}), 0)
                      FROM {{$table}} p
                     WHERE {$owner['sql']}
                       AND p.{$datefield} BETWEEN :monthstart AND :monthend
                       {$statussql}";
            $monthly = (float) $this->db->get_field_sql($sql, array_merge(
                $owner['params'],
                ['monthstart' => $start, 'monthend' => $end]
            ));
        }

        $pending = 0;
        if ($statusfield) {
            $sql = "SELECT COUNT(1)
                      FROM {{$table}} p
                     WHERE {$owner['sql']}
                       AND LOWER(p.{$statusfield}) IN
                           ('pending','unpaid','overdue','bekliyor',
                            'odenmedi','ödenmedi','gecikti','gecikmiş')";
            $pending = (int) $this->db->get_field_sql($sql, $owner['params']);
        }

        return ['monthly' => $monthly, 'pending' => $pending];
    }

    private function exam_summary(int $userid): array {
        $table = 'local_qubexa_student_exams';
        if (!$this->table_exists($table)) {
            return ['average' => 0.0];
        }

        $scorefield = $this->first_field($table, ['net', 'score', 'result', 'grade']);
        if (!$scorefield) {
            return ['average' => 0.0];
        }

        $owner = $this->owner_sql($table, $userid, 'e');
        $sql = "SELECT COALESCE(AVG(e.{$scorefield}), 0)
                  FROM {{$table}} e
                 WHERE {$owner['sql']}";

        return [
            'average' => (float) $this->db->get_field_sql($sql, $owner['params']),
        ];
    }

    private function lesson_summary(int $userid): array {
        $table = 'local_qubexa_student_lessons';
        if (!$this->table_exists($table)) {
            return ['count' => 0, 'items' => []];
        }

        $datefield = $this->first_field($table, [
            'lessondate', 'starttime', 'scheduledtime', 'timecreated'
        ]);
        if (!$datefield) {
            return ['count' => 0, 'items' => []];
        }

        $start = strtotime(userdate(time(), '%Y-%m-%d') . ' 00:00:00');
        $end = strtotime(userdate(time(), '%Y-%m-%d') . ' 23:59:59');
        $owner = $this->owner_sql($table, $userid, 'l');

        $sql = "SELECT l.*
                  FROM {{$table}} l
                 WHERE {$owner['sql']}
                   AND l.{$datefield} BETWEEN :daystart AND :dayend
              ORDER BY l.{$datefield} ASC";

        $records = $this->db->get_records_sql(
            $sql,
            array_merge($owner['params'], [
                'daystart' => $start,
                'dayend' => $end,
            ]),
            0,
            6
        );

        $items = [];
        foreach ($records as $record) {
            $items[] = [
                'time' => userdate(
                    (int) $record->{$datefield},
                    get_string('strftimetime24', 'langconfig')
                ),
                'title' => s($this->record_value(
                    $record,
                    ['title', 'lessonname', 'subject', 'topic'],
                    get_string('dashboardlessonfallback', 'local_qubexa')
                )),
                'meta' => s($this->record_value(
                    $record,
                    ['description', 'notes', 'topic'],
                    ''
                )),
            ];
        }

        return ['count' => count($records), 'items' => $items];
    }

    private function recent_activity(int $userid): array {
        $sources = [
            ['table' => 'local_qubexa_student_notes', 'dates' => ['timemodified', 'timecreated'], 'label' => 'dashboardactivitynote', 'icon' => 'N'],
            ['table' => 'local_qubexa_student_exams', 'dates' => ['examdate', 'timecreated'], 'label' => 'dashboardactivityexam', 'icon' => 'S'],
            ['table' => 'local_qubexa_student_lessons', 'dates' => ['lessondate', 'timecreated'], 'label' => 'dashboardactivitylesson', 'icon' => 'D'],
            ['table' => 'local_qubexa_student_payments', 'dates' => ['paymentdate', 'paiddate', 'timecreated'], 'label' => 'dashboardactivitypayment', 'icon' => 'P'],
        ];

        $items = [];
        foreach ($sources as $source) {
            if (!$this->table_exists($source['table'])) {
                continue;
            }

            $datefield = $this->first_field($source['table'], $source['dates']);
            if (!$datefield) {
                continue;
            }

            $owner = $this->owner_sql($source['table'], $userid, 'a');
            $records = $this->db->get_records_sql(
                "SELECT a.* FROM {{$source['table']}} a
                  WHERE {$owner['sql']}
               ORDER BY a.{$datefield} DESC",
                $owner['params'],
                0,
                1
            );

            if (!$records) {
                continue;
            }

            $record = reset($records);
            $timestamp = (int) $record->{$datefield};
            $items[] = [
                'timestamp' => $timestamp,
                'icon' => $source['icon'],
                'title' => get_string($source['label'], 'local_qubexa'),
                'date' => userdate(
                    $timestamp,
                    get_string('strftimedatetimeshort', 'langconfig')
                ),
            ];
        }

        usort($items, static function(array $left, array $right): int {
            return $right['timestamp'] <=> $left['timestamp'];
        });

        return array_slice($items, 0, 6);
    }

    private function owner_conditions(string $table, int $userid): array {
        foreach (['userid', 'teacherid', 'ownerid'] as $field) {
            if ($this->field_exists($table, $field)) {
                return [$field => $userid];
            }
        }

        return ['id' => -1];
    }

    private function owner_sql(string $table, int $userid, string $alias): array {
        foreach (['userid', 'teacherid', 'ownerid'] as $field) {
            if ($this->field_exists($table, $field)) {
                return [
                    'sql' => "{$alias}.{$field} = :dashboardownerid",
                    'params' => ['dashboardownerid' => $userid],
                ];
            }
        }

        return ['sql' => '1 = 0', 'params' => []];
    }

    private function table_exists(string $table): bool {
        return $this->dbman->table_exists(new \xmldb_table($table));
    }

    private function field_exists(string $table, string $field): bool {
        return $this->dbman->field_exists(
            new \xmldb_table($table),
            new \xmldb_field($field)
        );
    }

    private function first_field(string $table, array $fields): ?string {
        foreach ($fields as $field) {
            if ($this->field_exists($table, $field)) {
                return $field;
            }
        }

        return null;
    }

    private function record_value(
        object $record,
        array $fields,
        string $fallback
    ): string {
        foreach ($fields as $field) {
            if (property_exists($record, $field)
                    && trim((string) $record->{$field}) !== '') {
                return (string) $record->{$field};
            }
        }

        return $fallback;
    }
}
