<?php
require_once(__DIR__ . '/../../config.php');
require_login();

$context = context_system::instance();
require_capability('local/qubexa:view', $context);

$page = optional_param('page', \local_qubexa\workspace::DEFAULT_PAGE, PARAM_ALPHA);
if (!in_array($page, \local_qubexa\workspace::allowed_pages(), true)) {
    $page = \local_qubexa\workspace::DEFAULT_PAGE;
}

$PAGE->set_url(\local_qubexa\workspace::page_url($page));
$PAGE->set_context($context);
$PAGE->set_pagelayout('embedded');
$PAGE->set_title(get_string($page, 'local_qubexa'));
$PAGE->set_heading(get_string('pluginname', 'local_qubexa'));
$PAGE->add_body_class('qubexa-app');

$title = get_string($page, 'local_qubexa');
$subtitle = get_string($page . 'subtitle', 'local_qubexa');
$content = '';

switch ($page) {
    case 'dashboard':
        $dashboardcontext = [
            'firstname' => s($USER->firstname ?: fullname($USER)),
            'date' => userdate(time(), get_string('strftimedaydate','langconfig')),
            'scheduleitems' => [
                ['time'=>'09:00','title'=>'7/A Matematik','meta'=>get_string('samplelesson1','local_qubexa')],
                ['time'=>'11:00','title'=>'8/B Matematik','meta'=>get_string('samplelesson2','local_qubexa')],
                ['time'=>'14:00','title'=>'TYT Grubu','meta'=>get_string('samplelesson3','local_qubexa')],
            ],
            'quickactions' => [
                ['title'=>get_string('newstudent','local_qubexa'),'description'=>get_string('newstudentdesc','local_qubexa'),'icon'=>'👤','url'=>(new moodle_url('/local/qubexa_students/edit.php'))->out(false)],
                ['title'=>get_string('newmaterial','local_qubexa'),'description'=>get_string('newmaterialdesc','local_qubexa'),'icon'=>'📄','url'=>\local_qubexa\workspace::page_url('knowledge')->out(false)],
                ['title'=>get_string('newexam','local_qubexa'),'description'=>get_string('newexamdesc','local_qubexa'),'icon'=>'📝','url'=>\local_qubexa\workspace::page_url('assessment')->out(false)],
                ['title'=>get_string('calendar','local_qubexa'),'description'=>get_string('calendardesc','local_qubexa'),'icon'=>'📅','url'=>\local_qubexa\workspace::page_url('calendar')->out(false)],
            ],
        ];
        $content = $OUTPUT->render_from_template('local_qubexa/dashboard_content', $dashboardcontext);
        break;

    case 'students':
        if (!\core_component::get_component_directory('local_qubexa_students')) {
            $content = \local_qubexa\workspace::placeholder($page);
        } else {
            require_once($CFG->dirroot . '/local/qubexa_students/lib.php');
            $content = local_qubexa_students_render_workspace_page();
        }
        break;

    case 'calendar':
        $content = $OUTPUT->render_from_template('local_qubexa/placeholder', [
            'icon'=>'📅','title'=>$title,'message'=>get_string('calendarintegration','local_qubexa'),
            'actionurl'=>(new moodle_url('/calendar/view.php'))->out(false),
            'actionlabel'=>get_string('openmoodlecalendar','local_qubexa'),
        ]);
        break;

    default:
        $content = \local_qubexa\workspace::placeholder($page);
}

$appcontext = array_merge(
    \local_qubexa\workspace::context($page, $title, $subtitle),
    ['content' => $content]
);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_qubexa/workspace', $appcontext);
echo $OUTPUT->footer();
