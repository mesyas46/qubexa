<?php
namespace local_qubexa_students\service;
defined('MOODLE_INTERNAL') || die();
use local_qubexa_students\repository\payment_repository;
use local_qubexa_students\repository\student_repository;
final class payment_service {
    private student_repository $students; private payment_repository $payments;
    private const STATUSES=['paid','pending','overdue','cancelled'];
    private const METHODS=['cash','bank','card','other'];
    public function __construct(?student_repository $students=null,?payment_repository $payments=null){$this->students=$students??new student_repository();$this->payments=$payments??new payment_repository();}
    private function require_owned_student(int $studentid,int $userid): \stdClass { $s=$this->students->find_owned_student($studentid,$userid); if(!$s) throw new \moodle_exception('invalidrecord','error'); return $s; }
    private function normalise_date(string $v): int { $d=\DateTimeImmutable::createFromFormat('!Y-m-d',$v); if(!$d||$d->format('Y-m-d')!==$v) throw new \invalid_parameter_exception('Geçerli bir ödeme tarihi girin.'); return $d->getTimestamp(); }
    private function money(float $a): string { return number_format($a,2,',','.') . ' ₺'; }
    private function status_label(string $s): string { return ['paid'=>'Ödendi','pending'=>'Bekliyor','overdue'=>'Gecikmiş','cancelled'=>'İptal'][$s]??$s; }
    private function method_label(string $m): string { return ['cash'=>'Nakit','bank'=>'Banka','card'=>'Kart','other'=>'Diğer'][$m]??$m; }
    private function view_model(\stdClass $r): array { return ['id'=>(int)$r->id,'paymentdate'=>userdate($r->paymentdate,get_string('strftimedate','langconfig')),'amountlabel'=>$this->money((float)$r->amount),'status'=>(string)$r->status,'statuslabel'=>$this->status_label((string)$r->status),'methodlabel'=>$this->method_label((string)$r->method),'description'=>(string)($r->description??'')]; }
    public function list_payments(int $studentid,int $userid): array {
        $this->require_owned_student($studentid,$userid); $items=[];$paid=0.0;$pending=0.0;$overdue=0.0;
        foreach($this->payments->find_for_student($studentid,$userid) as $r){$items[]=$this->view_model($r);if($r->status==='paid')$paid+=(float)$r->amount;elseif($r->status==='pending')$pending+=(float)$r->amount;elseif($r->status==='overdue')$overdue+=(float)$r->amount;}
        return ['items'=>$items,'summary'=>['paid'=>$this->money($paid),'pending'=>$this->money($pending),'overdue'=>$this->money($overdue),'receivable'=>$this->money($pending+$overdue)]];
    }
    public function add_payment(int $studentid,int $userid,string $date,float $amount,string $status,string $method,string $description=''): int {
        $this->require_owned_student($studentid,$userid); if($amount<=0) throw new \invalid_parameter_exception('Tutar sıfırdan büyük olmalıdır.'); if(!in_array($status,self::STATUSES,true)) throw new \invalid_parameter_exception('Geçersiz ödeme durumu.'); if(!in_array($method,self::METHODS,true)) throw new \invalid_parameter_exception('Geçersiz ödeme yöntemi.'); $now=time(); return $this->payments->create((object)['studentid'=>$studentid,'userid'=>$userid,'paymentdate'=>$this->normalise_date($date),'amount'=>round($amount,2),'status'=>$status,'method'=>$method,'description'=>trim($description),'timecreated'=>$now,'timemodified'=>$now]);
    }
    public function delete_payment(int $paymentid,int $userid): void { if(!$this->payments->delete_owned($paymentid,$userid)) throw new \moodle_exception('invalidrecord','error'); }
}
