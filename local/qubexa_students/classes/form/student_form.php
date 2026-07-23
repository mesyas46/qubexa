<?php
namespace local_qubexa_students\form;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');
class student_form extends \moodleform {
 public function definition(): void {
  $mform=$this->_form;
  $mform->addElement('hidden','id',0); $mform->setType('id',PARAM_INT);
  $mform->addElement('header','identity',get_string('studentidentity','local_qubexa_students'));
  $mform->addElement('text','firstname',get_string('firstname'),['maxlength'=>100]); $mform->setType('firstname',PARAM_TEXT); $mform->addRule('firstname',get_string('required'),'required',null,'client');
  $mform->addElement('text','lastname',get_string('lastname'),['maxlength'=>100]); $mform->setType('lastname',PARAM_TEXT); $mform->addRule('lastname',get_string('required'),'required',null,'client');
  $mform->addElement('text','phone',get_string('phone','local_qubexa_students'),['maxlength'=>30]); $mform->setType('phone',PARAM_TEXT);
  $mform->addElement('text','email',get_string('email'),['maxlength'=>255]); $mform->setType('email',PARAM_EMAIL);
  $mform->addElement('header','academic',get_string('academicinfo','local_qubexa_students'));
  $mform->addElement('text','grade',get_string('grade','local_qubexa_students'),['maxlength'=>50]); $mform->setType('grade',PARAM_TEXT);
  $mform->addElement('text','groupname',get_string('groupname','local_qubexa_students'),['maxlength'=>100]); $mform->setType('groupname',PARAM_TEXT);
  $mform->addElement('select','status',get_string('status','local_qubexa_students'),['active'=>get_string('active','local_qubexa_students'),'passive'=>get_string('passive','local_qubexa_students')]); $mform->setDefault('status','active');
  $mform->addElement('header','parent',get_string('parentinfo','local_qubexa_students'));
  $mform->addElement('text','parentname',get_string('parentname','local_qubexa_students'),['maxlength'=>200]); $mform->setType('parentname',PARAM_TEXT);
  $mform->addElement('text','parentphone',get_string('parentphone','local_qubexa_students'),['maxlength'=>30]); $mform->setType('parentphone',PARAM_TEXT);
  $mform->addElement('textarea','notes',get_string('notes','local_qubexa_students'),['rows'=>6,'maxlength'=>4000]); $mform->setType('notes',PARAM_TEXT);
  $this->add_action_buttons(true,get_string('savechanges'));
 }
}
