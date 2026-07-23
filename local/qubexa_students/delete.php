<?php
require_once(__DIR__ . '/../../config.php');
require_login(); require_sesskey();
$context=context_system::instance(); require_capability('local/qubexa_students:manage',$context);
$id=required_param('id',PARAM_INT);
$student=$DB->get_record('local_qubexa_students',['id'=>$id,'userid'=>$USER->id],'*',MUST_EXIST);
$DB->delete_records('local_qubexa_students',['id'=>$student->id]);
redirect(\local_qubexa\workspace::page_url('students'),get_string('studentdeleted','local_qubexa_students'),null,\core\output\notification::NOTIFY_SUCCESS);
