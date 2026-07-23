<?php
defined('MOODLE_INTERNAL') || die();

function local_qubexa_students_extend_navigation(global_navigation $navigation): void {
    // Qubexa Framework owns application navigation.
}

function local_qubexa_students_render_workspace_page(): string {
    global $DB, $USER;
    $context = context_system::instance();
    require_capability('local/qubexa_students:view', $context);

    $search = optional_param('search', '', PARAM_TEXT);
    $status = optional_param('status', '', PARAM_ALPHA);
    $params = ['userid' => $USER->id];
    $where = 'userid = :userid';
    if ($search !== '') {
        $like = '%' . $DB->sql_like_escape($search) . '%';
        $params['s1']=$like; $params['s2']=$like; $params['s3']=$like;
        $where .= ' AND (' . $DB->sql_like('firstname', ':s1', false) .
            ' OR ' . $DB->sql_like('lastname', ':s2', false) .
            ' OR ' . $DB->sql_like('groupname', ':s3', false) . ')';
    }
    if (in_array($status, ['active','passive'], true)) {
        $params['status']=$status; $where .= ' AND status = :status';
    }
    $students=$DB->get_records_select('local_qubexa_students',$where,$params,'lastname ASC, firstname ASC');

    $html=html_writer::start_div('qubexa-students-shell');
    $html.=html_writer::start_div('qubexa-students-hero');
    $html.=html_writer::start_div();
    $html.=html_writer::tag('p',get_string('studentsintro','local_qubexa_students'));
    $html.=html_writer::end_div();
    $html.=html_writer::link(new moodle_url('/local/qubexa_students/edit.php'),'＋ '.get_string('addstudent','local_qubexa_students'),['class'=>'btn btn-primary qubexa-primary-action']);
    $html.=html_writer::end_div();

    $html.=html_writer::start_div('qubexa-toolbar');
    $html.=html_writer::start_tag('form',['method'=>'get','action'=>\local_qubexa\workspace::page_url('students')->out(false),'class'=>'qubexa-filter-form']);
    $html.=html_writer::empty_tag('input',['type'=>'hidden','name'=>'page','value'=>'students']);
    $html.=html_writer::empty_tag('input',['type'=>'search','name'=>'search','value'=>$search,'placeholder'=>get_string('searchplaceholder','local_qubexa_students'),'class'=>'form-control']);
    $html.=html_writer::select([''=>get_string('allstatuses','local_qubexa_students'),'active'=>get_string('active','local_qubexa_students'),'passive'=>get_string('passive','local_qubexa_students')],'status',$status,false,['class'=>'form-control']);
    $html.=html_writer::tag('button',get_string('filter','local_qubexa_students'),['type'=>'submit','class'=>'btn btn-secondary']);
    $html.=html_writer::end_tag('form');
    $html.=html_writer::tag('div',get_string('studentcount','local_qubexa_students',count($students)),['class'=>'qubexa-student-count']);
    $html.=html_writer::end_div();

    if (!$students) {
        $html.=html_writer::start_div('qubexa-empty-state');
        $html.=html_writer::tag('div','👥',['class'=>'qubexa-empty-icon']);
        $html.=html_writer::tag('h2',get_string('nostudents','local_qubexa_students'));
        $html.=html_writer::tag('p',get_string('nostudentsdesc','local_qubexa_students'));
        $html.=html_writer::link(new moodle_url('/local/qubexa_students/edit.php'),get_string('addfirststudent','local_qubexa_students'),['class'=>'btn btn-primary']);
        $html.=html_writer::end_div();
    } else {
        $table=new html_table(); $table->attributes['class']='table qubexa-students-table';
        $table->head=[get_string('student','local_qubexa_students'),get_string('grade','local_qubexa_students'),get_string('groupname','local_qubexa_students'),get_string('phone','local_qubexa_students'),get_string('parent','local_qubexa_students'),get_string('status','local_qubexa_students'),get_string('actions','local_qubexa_students')];
        foreach($students as $student){
            $fullname=format_string(trim($student->firstname.' '.$student->lastname));
            $initials=core_text::strtoupper(core_text::substr($student->firstname,0,1).core_text::substr($student->lastname,0,1));
            $studentcell=html_writer::start_div('qubexa-student-name').html_writer::tag('span',$initials,['class'=>'qubexa-avatar']).html_writer::tag('strong',$fullname).html_writer::end_div();
            $badgeclass=$student->status==='active'?'is-active':'is-passive';
            $statustext=$student->status==='active'?get_string('active','local_qubexa_students'):get_string('passive','local_qubexa_students');
            $editurl=new moodle_url('/local/qubexa_students/edit.php',['id'=>$student->id]);
            $deleteurl=new moodle_url('/local/qubexa_students/delete.php',['id'=>$student->id,'sesskey'=>sesskey()]);
            $actions=html_writer::link($editurl,get_string('edit'),['class'=>'qubexa-action-link']).html_writer::link($deleteurl,get_string('delete'),['class'=>'qubexa-action-link is-danger','onclick'=>"return confirm('".get_string('confirmdelete','local_qubexa_students')."');"]);
            $table->data[]=[$studentcell,s($student->grade),s($student->groupname),s($student->phone),s($student->parentname),html_writer::tag('span',$statustext,['class'=>'qubexa-status-badge '.$badgeclass]),$actions];
        }
        $html.=html_writer::start_div('table-responsive qubexa-table-wrap').html_writer::table($table).html_writer::end_div();
    }
    return $html.html_writer::end_div();
}
