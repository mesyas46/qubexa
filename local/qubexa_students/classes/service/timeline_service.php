<?php
namespace local_qubexa_students\service;

defined('MOODLE_INTERNAL') || die();

use local_qubexa_students\repository\timeline_repository;

/**
 * Builds a unified student activity timeline.
 *
 * @package local_qubexa_students
 */
final class timeline_service {
    private timeline_repository $repository;

    public function __construct(
        ?timeline_repository $repository = null
    ) {
        $this->repository = $repository ??
            new timeline_repository();
    }

    /**
     * Require a student owned by the teacher.
     *
     * @param int $studentid
     * @param int $userid
     * @return \stdClass
     */
    private function require_owned_student(
        int $studentid,
        int $userid
    ): \stdClass {
        $student = $this->repository->find_owned_student(
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

    /**
     * Format one timeline date.
     *
     * @param int $timestamp
     * @param bool $withtime
     * @return string
     */
    private function date_label(
        int $timestamp,
        bool $withtime = false
    ): string {
        $format = $withtime
            ? get_string('strftimedatetime', 'langconfig')
            : get_string('strftimedate', 'langconfig');

        return userdate($timestamp, $format);
    }

    /**
     * Build all available events.
     *
     * @param int $studentid
     * @param int $userid
     * @param string $filter
     * @param int $limit
     * @return array
     */
    public function build(
        int $studentid,
        int $userid,
        string $filter = 'all',
        int $limit = 100
    ): array {
        $student = $this->require_owned_student(
            $studentid,
            $userid
        );

        $allowedfilters = [
            'all',
            'note',
            'exam',
            'lesson',
            'student',
        ];

        if (!in_array($filter, $allowedfilters, true)) {
            $filter = 'all';
        }

        $events = [];

        if ($filter === 'all' || $filter === 'note') {
            foreach (
                $this->repository->find_notes(
                    $studentid,
                    $userid
                ) as $note
            ) {
                $timestamp = (int) $note->timecreated;

                $events[] = [
                    'id' => 'note-' . (int) $note->id,
                    'type' => 'note',
                    'icon' => '📝',
                    'title' => 'Öğretmen Notu',
                    'subtitle' => '',
                    'content' => (string) $note->note,
                    'meta' => '',
                    'timestamp' => $timestamp,
                    'date' => $this->date_label(
                        $timestamp,
                        true
                    ),
                ];
            }
        }

        if ($filter === 'all' || $filter === 'exam') {
            foreach (
                $this->repository->find_exams(
                    $studentid,
                    $userid
                ) as $exam
            ) {
                $timestamp = (int) $exam->examdate;
                $net = number_format(
                    (float) $exam->net,
                    2,
                    ',',
                    '.'
                );

                $events[] = [
                    'id' => 'exam-' . (int) $exam->id,
                    'type' => 'exam',
                    'icon' => '📊',
                    'title' => (string) $exam->examname,
                    'subtitle' => 'Sınav sonucu',
                    'content' => (string) (
                        $exam->description ?? ''
                    ),
                    'meta' =>
                        'Doğru: ' .
                        (int) $exam->correctcount .
                        ' · Yanlış: ' .
                        (int) $exam->wrongcount .
                        ' · Boş: ' .
                        (int) $exam->blankcount .
                        ' · Net: ' .
                        $net,
                    'timestamp' => $timestamp,
                    'date' => $this->date_label($timestamp),
                ];
            }
        }

        if ($filter === 'all' || $filter === 'lesson') {
            foreach (
                $this->repository->find_lessons(
                    $studentid,
                    $userid
                ) as $lesson
            ) {
                $timestamp = (int) $lesson->lessondate;
                $statuslabels = [
                    'attended' => 'Katıldı',
                    'late' => 'Geç geldi',
                    'absent' => 'Gelmedi',
                    'excused' => 'İzinli',
                ];
                $status = (string) $lesson->status;
                $statuslabel =
                    $statuslabels[$status] ?? $status;

                $contentparts = [];

                if (!empty($lesson->teachernote)) {
                    $contentparts[] =
                        (string) $lesson->teachernote;
                }

                if (!empty($lesson->homeworkgiven)) {
                    $homework = trim(
                        (string) (
                            $lesson->homeworknote ?? ''
                        )
                    );

                    $contentparts[] = $homework !== ''
                        ? 'Ödev: ' . $homework
                        : 'Ödev verildi.';
                }

                if (!empty($lesson->nextlesson)) {
                    $contentparts[] =
                        'Sonraki ders: ' .
                        (string) $lesson->nextlesson;
                }

                $events[] = [
                    'id' => 'lesson-' . (int) $lesson->id,
                    'type' => 'lesson',
                    'icon' => '📚',
                    'title' => (string) $lesson->topic,
                    'subtitle' => 'Ders kaydı',
                    'content' => implode(
                        "\n",
                        $contentparts
                    ),
                    'meta' =>
                        (string) $lesson->starttime .
                        '–' .
                        (string) $lesson->endtime .
                        ' · ' .
                        (int) $lesson->duration .
                        ' dk. · ' .
                        $statuslabel,
                    'timestamp' => $timestamp,
                    'date' => $this->date_label($timestamp),
                ];
            }
        }

        if ($filter === 'all' || $filter === 'student') {
            $timestamp = (int) $student->timecreated;

            if ($timestamp > 0) {
                $events[] = [
                    'id' => 'student-' . (int) $student->id,
                    'type' => 'student',
                    'icon' => '👤',
                    'title' => 'Öğrenci Oluşturuldu',
                    'subtitle' => 'Öğrenci kaydı',
                    'content' =>
                        trim(
                            (string) $student->firstname .
                            ' ' .
                            (string) $student->lastname
                        ) .
                        ' Qubexa öğrenci yönetimine eklendi.',
                    'meta' => '',
                    'timestamp' => $timestamp,
                    'date' => $this->date_label(
                        $timestamp,
                        true
                    ),
                ];
            }
        }

        usort(
            $events,
            static function (
                array $first,
                array $second
            ): int {
                if (
                    $first['timestamp'] ===
                    $second['timestamp']
                ) {
                    return strcmp(
                        $second['id'],
                        $first['id']
                    );
                }

                return $second['timestamp'] <=>
                    $first['timestamp'];
            }
        );

        $limit = max(1, min($limit, 250));
        $events = array_slice($events, 0, $limit);

        $counts = [
            'all' => count($events),
            'note' => 0,
            'exam' => 0,
            'lesson' => 0,
            'student' => 0,
        ];

        foreach ($events as $event) {
            if (isset($counts[$event['type']])) {
                $counts[$event['type']]++;
            }
        }

        return [
            'items' => array_values($events),
            'counts' => $counts,
        ];
    }
}
