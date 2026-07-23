<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/classes/form/student_form.php');
require_login();
$context=context_system::instance();
require_capability('local/qubexa_students:manage',$context);
$id=optional_param('id',0,PARAM_INT);
$PAGE->set_url(new moodle_url('/local/qubexa_students/edit.php',['id'=>$id]));
$PAGE->set_context($context); $PAGE->set_pagelayout('embedded');
$PAGE->set_title($id?get_string('editstudent','local_qubexa_students'):get_string('addstudent','local_qubexa_students'));
$PAGE->add_body_class('qubexa-app');
$student=null;
if($id){$student=$DB->get_record('local_qubexa_students',['id'=>$id,'userid'=>$USER->id],'*',MUST_EXIST);}
$form=new \local_qubexa_students\form\student_form(null,['student'=>$student]);
if($form->is_cancelled()){redirect(\local_qubexa\workspace::page_url('students'));}
if($data=$form->get_data()){
 $record=(object)['userid'=>$USER->id,'firstname'=>trim($data->firstname),'lastname'=>trim($data->lastname),'phone'=>trim($data->phone),'email'=>trim($data->email),'parentname'=>trim($data->parentname),'parentphone'=>trim($data->parentphone),'grade'=>trim($data->grade),'groupname'=>trim($data->groupname),'status'=>$data->status,'notes'=>$data->notes,'timemodified'=>time()];
 if(!empty($data->id)){$existing=$DB->get_record('local_qubexa_students',['id'=>$data->id,'userid'=>$USER->id],'*',MUST_EXIST);$record->id=$existing->id;$DB->update_record('local_qubexa_students',$record);$message=get_string('studentupdated','local_qubexa_students');}
 else{$record->timecreated=time();$DB->insert_record('local_qubexa_students',$record);$message=get_string('studentcreated','local_qubexa_students');}
 redirect(\local_qubexa\workspace::page_url('students'),$message,null,\core\output\notification::NOTIFY_SUCCESS);
}
if($student){$form->set_data($student);}
ob_start();
echo html_writer::start_div('qubexa-form-shell');
echo html_writer::tag('h2',$id?get_string('editstudent','local_qubexa_students'):get_string('addstudent','local_qubexa_students'));
echo html_writer::tag('p',get_string('formintro','local_qubexa_students'));
$form->display(); echo html_writer::end_div();
$content=ob_get_clean();
$appcontext=array_merge(\local_qubexa\workspace::context('students',$id?get_string('editstudent','local_qubexa_students'):get_string('addstudent','local_qubexa_students'),get_string('studentsintro','local_qubexa_students')),['content'=>$content]);
echo $OUTPUT->header(); echo $OUTPUT->render_from_template('local_qubexa/workspace',$appcontext); echo $OUTPUT->footer();
