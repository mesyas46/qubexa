<?php
namespace local_qubexa_students\service;

defined('MOODLE_INTERNAL') || die();

use local_qubexa_students\repository\exam_repository;
use local_qubexa_students\repository\student_repository;

/**
 * Student exam application service.
 *
 * @package local_qubexa_students
 */
final class exam_service {
    private student_repository $students;
    private exam_repository $exams;

    public function __construct(
        ?student_repository $students = null,
        ?exam_repository $exams = null
    ) {
        $this->students = $students ?? new student_repository();
        $this->exams = $exams ?? new exam_repository();
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
            throw new \moodle_exception('invalidrecord', 'error');
        }

        return $student;
    }

    private function normalise_date(string $examdate): int {
        $date = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $examdate
        );

        if (!$date || $date->format('Y-m-d') !== $examdate) {
            throw new \invalid_parameter_exception(
                'Geçerli bir sınav tarihi girin.'
            );
        }

        return $date->getTimestamp();
    }

    private function view_model(\stdClass $record): array {
        return [
            'id' => (int) $record->id,
            'examname' => format_string($record->examname),
            'examdate' => userdate(
                $record->examdate,
                get_string('strftimedate', 'langconfig')
            ),
            'correct' => (int) $record->correctcount,
            'wrong' => (int) $record->wrongcount,
            'blank' => (int) $record->blankcount,
            'net' => number_format((float) $record->net, 2, ',', '.'),
            'description' => s($record->description ?? ''),
        ];
    }

    public function list_exams(int $studentid, int $userid): array {
        $this->require_owned_student($studentid, $userid);

        $records = $this->exams->find_for_student(
            $studentid,
            $userid
        );

        $items = [];
        $nets = [];

        foreach ($records as $record) {
            $items[] = $this->view_model($record);
            $nets[] = (float) $record->net;
        }

        $average = $nets
            ? array_sum($nets) / count($nets)
            : 0.0;

        return [
            'items' => $items,
            'summary' => [
                'count' => count($items),
                'average' => number_format($average, 2, ',', '.'),
                'highest' => number_format(
                    $nets ? max($nets) : 0,
                    2,
                    ',',
                    '.'
                ),
                'latest' => number_format(
                    $nets ? $nets[0] : 0,
                    2,
                    ',',
                    '.'
                ),
            ],
        ];
    }

    public function add_exam(
        int $studentid,
        int $userid,
        string $examname,
        string $examdate,
        int $correct,
        int $wrong,
        int $blank,
        string $description = ''
    ): int {
        $this->require_owned_student($studentid, $userid);

        $examname = trim($examname);
        $description = trim($description);

        if ($examname === '') {
            throw new \invalid_parameter_exception(
                'Sınav adı boş bırakılamaz.'
            );
        }

        if (\core_text::strlen($examname) > 255) {
            throw new \invalid_parameter_exception(
                'Sınav adı en fazla 255 karakter olabilir.'
            );
        }

        if (\core_text::strlen($description) > 2000) {
            throw new \invalid_parameter_exception(
                'Açıklama en fazla 2000 karakter olabilir.'
            );
        }

        foreach ([$correct, $wrong, $blank] as $value) {
            if ($value < 0) {
                throw new \invalid_parameter_exception(
                    'Doğru, yanlış ve boş değerleri negatif olamaz.'
                );
            }
        }

        $net = round($correct - ($wrong / 4), 2);
        $now = time();

        return $this->exams->create((object) [
            'studentid' => $studentid,
            'userid' => $userid,
            'examname' => $examname,
            'examdate' => $this->normalise_date($examdate),
            'correctcount' => $correct,
            'wrongcount' => $wrong,
            'blankcount' => $blank,
            'net' => $net,
            'description' => $description,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);
    }

    public function delete_exam(
        int $examid,
        int $studentid,
        int $userid
    ): void {
        $this->require_owned_student($studentid, $userid);

        if (!$this->exams->delete_owned(
            $examid,
            $studentid,
            $userid
        )) {
            throw new \moodle_exception('invalidrecord', 'error');
        }
    }
}
