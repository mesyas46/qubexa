<?php
namespace local_qubexa_students\service;

defined('MOODLE_INTERNAL') || die();

use local_qubexa_students\repository\homework_repository;
use local_qubexa_students\repository\student_repository;

/**
 * Student homework rules and view models.
 *
 * @package local_qubexa_students
 */
final class homework_service {
    private const STATUSES = ['pending', 'completed'];

    private student_repository $students;
    private homework_repository $homeworks;

    public function __construct(
        ?student_repository $students = null,
        ?homework_repository $homeworks = null
    ) {
        $this->students = $students ?? new student_repository();
        $this->homeworks = $homeworks ?? new homework_repository();
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

    private function normalise_date(string $value): int {
        $date = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $value,
            \core_date::get_user_timezone_object()
        );

        $errors = \DateTimeImmutable::getLastErrors();

        if (
            !$date ||
            (is_array($errors) && (
                $errors['warning_count'] > 0 ||
                $errors['error_count'] > 0
            )) ||
            $date->format('Y-m-d') !== $value
        ) {
            throw new \invalid_parameter_exception(
                'Geçerli bir son tarih girin.'
            );
        }

        return $date->getTimestamp();
    }

    private function today_start(): int {
        return (new \DateTimeImmutable(
            'today',
            \core_date::get_user_timezone_object()
        ))->getTimestamp();
    }

    private function view_model(
        \stdClass $record,
        int $today
    ): array {
        $status = (string) $record->status;
        $overdue = $status === 'pending' &&
            (int) $record->duedate < $today;

        return [
            'id' => (int) $record->id,
            'title' => format_string((string) $record->title),
            'duedate' => userdate(
                (int) $record->duedate,
                get_string('strftimedate', 'langconfig')
            ),
            'status' => $status,
            'statuslabel' => $overdue
                ? 'Gecikmiş'
                : ($status === 'completed'
                    ? 'Tamamlandı'
                    : 'Bekliyor'),
            'statusclass' => $overdue ? 'overdue' : $status,
            'iscompleted' => $status === 'completed',
            'description' => (string) ($record->description ?? ''),
        ];
    }

    public function list_homeworks(
        int $studentid,
        int $userid
    ): array {
        $this->require_owned_student($studentid, $userid);

        $items = [];
        $summary = [
            'total' => 0,
            'pending' => 0,
            'completed' => 0,
            'overdue' => 0,
        ];
        $today = $this->today_start();

        foreach (
            $this->homeworks->find_for_student(
                $studentid,
                $userid
            ) as $record
        ) {
            $item = $this->view_model($record, $today);
            $items[] = $item;
            $summary['total']++;

            if ($item['statusclass'] === 'overdue') {
                $summary['overdue']++;
            } elseif ($item['iscompleted']) {
                $summary['completed']++;
            } else {
                $summary['pending']++;
            }
        }

        return [
            'items' => $items,
            'summary' => $summary,
        ];
    }

    public function add_homework(
        int $studentid,
        int $userid,
        string $title,
        string $duedate,
        string $description = ''
    ): int {
        $this->require_owned_student($studentid, $userid);

        $title = trim($title);
        $description = trim($description);

        if ($title === '' || \core_text::strlen($title) > 255) {
            throw new \invalid_parameter_exception(
                'Ödev başlığı 1-255 karakter olmalıdır.'
            );
        }

        if (\core_text::strlen($description) > 2000) {
            throw new \invalid_parameter_exception(
                'Açıklama en fazla 2000 karakter olabilir.'
            );
        }

        $now = time();

        return $this->homeworks->create((object) [
            'studentid' => $studentid,
            'userid' => $userid,
            'title' => $title,
            'duedate' => $this->normalise_date($duedate),
            'status' => 'pending',
            'description' => $description,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);
    }

    public function update_status(
        int $homeworkid,
        int $studentid,
        int $userid,
        string $status
    ): void {
        $this->require_owned_student($studentid, $userid);

        if (!in_array($status, self::STATUSES, true)) {
            throw new \invalid_parameter_exception(
                'Geçersiz ödev durumu.'
            );
        }

        if (!$this->homeworks->update_status_owned(
            $homeworkid,
            $studentid,
            $userid,
            $status
        )) {
            throw new \moodle_exception('invalidrecord', 'error');
        }
    }

    public function delete_homework(
        int $homeworkid,
        int $studentid,
        int $userid
    ): void {
        $this->require_owned_student($studentid, $userid);

        if (!$this->homeworks->delete_owned(
            $homeworkid,
            $studentid,
            $userid
        )) {
            throw new \moodle_exception('invalidrecord', 'error');
        }
    }
}
