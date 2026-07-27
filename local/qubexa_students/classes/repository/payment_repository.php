<?php
namespace local_qubexa_students\repository;
defined('MOODLE_INTERNAL') || die();
final class payment_repository {
    public function find_for_student(int $studentid, int $userid): array {
        global $DB;
        return $DB->get_records('local_qubexa_student_payments', ['studentid'=>$studentid,'userid'=>$userid], 'paymentdate DESC, id DESC');
    }
    public function create(\stdClass $record): int { global $DB; return (int)$DB->insert_record('local_qubexa_student_payments',$record); }
    public function delete_owned(int $paymentid,int $userid): bool {
        global $DB;
        if (!$DB->record_exists('local_qubexa_student_payments',['id'=>$paymentid,'userid'=>$userid])) return false;
        return $DB->delete_records('local_qubexa_student_payments',['id'=>$paymentid,'userid'=>$userid]);
    }
}
