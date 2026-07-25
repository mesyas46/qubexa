<?php
namespace local_qubexa_students\service;

defined('MOODLE_INTERNAL') || die();

use local_qubexa_students\repository\note_repository;
use local_qubexa_students\repository\student_repository;

final class note_service {
    private student_repository $students;
    private note_repository $notes;

    public function __construct(
        ?student_repository $students = null,
        ?note_repository $notes = null
    ) {
        $this->students = $students ?? new student_repository();
        $this->notes = $notes ?? new note_repository();
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

    public function list_notes(int $studentid, int $userid): array {
        $this->require_owned_student($studentid, $userid);

        $records = $this->notes->find_for_student(
            $studentid,
            $userid
        );

        $items = [];

        foreach ($records as $record) {
            $items[] = [
                'id' => (int) $record->id,
                'note' => $record->note,
                'date' => userdate(
                    $record->timecreated,
                    get_string('strftimedatetime', 'langconfig')
                ),
            ];
        }

        return $items;
    }

    public function add_note(
        int $studentid,
        int $userid,
        string $note
    ): int {
        $this->require_owned_student($studentid, $userid);

        $note = trim($note);

        if ($note === '') {
            throw new \invalid_parameter_exception(
                'Not metni boş bırakılamaz.'
            );
        }

        if (\core_text::strlen($note) > 5000) {
            throw new \invalid_parameter_exception(
                'Not metni en fazla 5000 karakter olabilir.'
            );
        }

        return $this->notes->create($studentid, $userid, $note);
    }

    public function delete_note(int $noteid, int $userid): void {
        if (!$this->notes->delete_owned($noteid, $userid)) {
            throw new \moodle_exception('invalidrecord', 'error');
        }
    }
}
