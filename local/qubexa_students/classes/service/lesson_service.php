<?php
namespace local_qubexa_students\service;

defined('MOODLE_INTERNAL') || die();

use local_qubexa_students\repository\lesson_repository;
use local_qubexa_students\repository\student_repository;

/**
 * Student lesson application service.
 *
 * @package local_qubexa_students
 */
final class lesson_service {
    private student_repository $students;
    private lesson_repository $lessons;

    private const ALLOWED_STATUSES = [
        'attended',
        'late',
        'absent',
        'excused',
    ];

    public function __construct(
        ?student_repository $students = null,
        ?lesson_repository $lessons = null
    ) {
        $this->students = $students ?? new student_repository();
        $this->lessons = $lessons ?? new lesson_repository();
    }

    private function require_owned_student(
        int $studentid,
        int $userid
    ): \stdClass {
        $student = $this->students->find_owned_student(
            $studentid,
            $userid
        );

        if (!$student) {
            throw new \moodle_exception(
                'invalidrecord',
                'error'
            );
        }

        return $student;
    }

    private function normalise_date(string $lessondate): int {
        $date = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $lessondate
        );

        if (
            !$date ||
            $date->format('Y-m-d') !== $lessondate
        ) {
            throw new \invalid_parameter_exception(
                'Geçerli bir ders tarihi girin.'
            );
        }

        return $date->getTimestamp();
    }

    private function normalise_time(string $value): string {
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value)) {
            throw new \invalid_parameter_exception(
                'Saat bilgisi HH:MM biçiminde olmalıdır.'
            );
        }

        return $value;
    }

    private function calculate_duration(
        string $starttime,
        string $endtime
    ): int {
        [$starthour, $startminute] = array_map(
            'intval',
            explode(':', $starttime)
        );
        [$endhour, $endminute] = array_map(
            'intval',
            explode(':', $endtime)
        );

        $start = ($starthour * 60) + $startminute;
        $end = ($endhour * 60) + $endminute;

        if ($end <= $start) {
            throw new \invalid_parameter_exception(
                'Bitiş saati başlangıç saatinden sonra olmalıdır.'
            );
        }

        return $end - $start;
    }

    private function status_label(string $status): string {
        $labels = [
            'attended' => 'Katıldı',
            'late' => 'Geç geldi',
            'absent' => 'Gelmedi',
            'excused' => 'İzinli',
        ];

        return $labels[$status] ?? $status;
    }

    private function view_model(\stdClass $record): array {
        return [
            'id' => (int) $record->id,
            'lessondate' => userdate(
                $record->lessondate,
                get_string('strftimedate', 'langconfig')
            ),
            'starttime' => (string) $record->starttime,
            'endtime' => (string) $record->endtime,
            'duration' => (int) $record->duration,
            'durationlabel' => (int) $record->duration . ' dk.',
            'topic' => (string) $record->topic,
            'status' => (string) $record->status,
            'statuslabel' => $this->status_label(
                (string) $record->status
            ),
            'homeworkgiven' => (bool) $record->homeworkgiven,
            'homeworknote' => (string) (
                $record->homeworknote ?? ''
            ),
            'teachernote' => (string) (
                $record->teachernote ?? ''
            ),
            'nextlesson' => (string) (
                $record->nextlesson ?? ''
            ),
        ];
    }

    public function list_lessons(
        int $studentid,
        int $userid
    ): array {
        $this->require_owned_student($studentid, $userid);

        $records = $this->lessons->find_for_student(
            $studentid,
            $userid
        );

        $items = [];
        $attended = 0;
        $absent = 0;
        $thismonth = 0;
        $latest = '';

        $monthstart = strtotime(
            date('Y-m-01 00:00:00')
        );
        $monthend = strtotime(
            date('Y-m-t 23:59:59')
        );

        foreach ($records as $record) {
            $items[] = $this->view_model($record);

            if (
                in_array(
                    $record->status,
                    ['attended', 'late'],
                    true
                )
            ) {
                $attended++;
            }

            if ($record->status === 'absent') {
                $absent++;
            }

            if (
                $record->lessondate >= $monthstart &&
                $record->lessondate <= $monthend
            ) {
                $thismonth++;
            }

            if ($latest === '') {
                $latest = userdate(
                    $record->lessondate,
                    get_string(
                        'strftimedateshort',
                        'langconfig'
                    )
                );
            }
        }

        $total = count($items);
        $attendancebase = $attended + $absent;
        $attendance = $attendancebase > 0
            ? round(($attended / $attendancebase) * 100)
            : 0;

        return [
            'items' => $items,
            'summary' => [
                'total' => $total,
                'attendance' => $attendance . '%',
                'absent' => $absent,
                'latest' => $latest ?: '—',
                'thismonth' => $thismonth,
            ],
        ];
    }

    public function add_lesson(
        int $studentid,
        int $userid,
        string $lessondate,
        string $starttime,
        string $endtime,
        string $topic,
        string $status,
        bool $homeworkgiven,
        string $homeworknote = '',
        string $teachernote = '',
        string $nextlesson = ''
    ): int {
        $this->require_owned_student($studentid, $userid);

        $topic = trim($topic);
        $homeworknote = trim($homeworknote);
        $teachernote = trim($teachernote);
        $nextlesson = trim($nextlesson);

        if (!$homeworkgiven) {
            $homeworknote = '';
        }

        if ($topic === '') {
            throw new \invalid_parameter_exception(
                'Ders konusu boş bırakılamaz.'
            );
        }

        if (\core_text::strlen($topic) > 255) {
            throw new \invalid_parameter_exception(
                'Ders konusu en fazla 255 karakter olabilir.'
            );
        }

        foreach (
            [
                'Ödev açıklaması' => $homeworknote,
                'Öğretmen notu' => $teachernote,
                'Bir sonraki ders' => $nextlesson,
            ] as $label => $value
        ) {
            if (\core_text::strlen($value) > 2000) {
                throw new \invalid_parameter_exception(
                    $label . ' en fazla 2000 karakter olabilir.'
                );
            }
        }

        if (
            !in_array(
                $status,
                self::ALLOWED_STATUSES,
                true
            )
        ) {
            throw new \invalid_parameter_exception(
                'Geçersiz katılım durumu.'
            );
        }

        $starttime = $this->normalise_time($starttime);
        $endtime = $this->normalise_time($endtime);
        $duration = $this->calculate_duration(
            $starttime,
            $endtime
        );
        $now = time();

        return $this->lessons->create((object) [
            'studentid' => $studentid,
            'userid' => $userid,
            'lessondate' => $this->normalise_date(
                $lessondate
            ),
            'starttime' => $starttime,
            'endtime' => $endtime,
            'duration' => $duration,
            'topic' => $topic,
            'status' => $status,
            'homeworkgiven' => $homeworkgiven ? 1 : 0,
            'homeworknote' => $homeworknote,
            'teachernote' => $teachernote,
            'nextlesson' => $nextlesson,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);
    }

    public function delete_lesson(
        int $lessonid,
        int $studentid,
        int $userid
    ): void {
        $this->require_owned_student($studentid, $userid);

        if (
            !$this->lessons->delete_owned(
                $lessonid,
                $studentid,
                $userid
            )
        ) {
            throw new \moodle_exception(
                'invalidrecord',
                'error'
            );
        }
    }
}
