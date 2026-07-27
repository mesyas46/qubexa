<?php
define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../../config.php');
require_login();
$context=context_system::instance();$PAGE->set_context($context);$PAGE->set_url(new moodle_url('/local/qubexa_students/ajax/payments.php'));
require_capability('local/qubexa_students:view',$context);
header('Content-Type: application/json; charset=utf-8');
try {
 $action=required_param('action',PARAM_ALPHA);$service=new \local_qubexa_students\service\payment_service();
 if($action==='list'){ $studentid=required_param('studentid',PARAM_INT); echo json_encode(['success'=>true,'data'=>$service->list_payments($studentid,(int)$USER->id)]); exit; }
 require_sesskey();
 if($action==='add'){ $studentid=required_param('studentid',PARAM_INT);$service->add_payment($studentid,(int)$USER->id,required_param('paymentdate',PARAM_RAW_TRIMMED),(float)required_param('amount',PARAM_FLOAT),required_param('status',PARAM_ALPHA),required_param('method',PARAM_ALPHA),optional_param('description','',PARAM_TEXT)); echo json_encode(['success'=>true,'data'=>$service->list_payments($studentid,(int)$USER->id)]); exit; }
 if($action==='delete'){ $studentid=required_param('studentid',PARAM_INT);$service->delete_payment(required_param('paymentid',PARAM_INT),(int)$USER->id); echo json_encode(['success'=>true,'data'=>$service->list_payments($studentid,(int)$USER->id)]); exit; }
 throw new \invalid_parameter_exception('Geçersiz işlem.');
} catch(\Throwable $e){http_response_code(400);echo json_encode(['success'=>false,'message'=>$e->getMessage()]);}
