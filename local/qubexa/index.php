<?php
require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/qubexa:view', $context);

$page = optional_param(
    'page',
    \local_qubexa\workspace::DEFAULT_PAGE,
    PARAM_ALPHA
);

$legacyaliases = [
    'students' => 'lessons',
    'groups' => 'classes',
    'knowledge' => 'office',
];

if (isset($legacyaliases[$page])) {
    $page = $legacyaliases[$page];
}

if (!in_array($page, \local_qubexa\workspace::allowed_pages(), true)) {
    $page = \local_qubexa\workspace::DEFAULT_PAGE;
}

$PAGE->set_url(\local_qubexa\workspace::page_url($page));
$PAGE->set_context($context);
$PAGE->set_pagelayout('embedded');
$PAGE->set_title(get_string($page, 'local_qubexa'));
$PAGE->set_heading(get_string('pluginname', 'local_qubexa'));
$PAGE->add_body_class('qubexa-app');

$PAGE->requires->css(
    new moodle_url('/local/qubexa/mobile.css')
);
$PAGE->requires->css(
    new moodle_url('/local/qubexa/launcher.css')
);

$PAGE->requires->js(
    new moodle_url('/local/qubexa/launcher.js'),
    true
);

$PAGE->requires->js(
    new moodle_url('/local/qubexa/mobile_menu.js'),
    true
);

$title = get_string($page, 'local_qubexa');
$subtitle = get_string($page . 'subtitle', 'local_qubexa');

$content = '';

switch ($page) {
    case 'dashboard':
        $PAGE->requires->css(
            new moodle_url('/local/qubexa/dashboard_v2.css')
        );

        $dashboard = new \local_qubexa\output\dashboard_page(
            (int) $USER->id
        );

        $content = $OUTPUT->render_from_template(
            'local_qubexa/dashboard_content',
            $dashboard->export_for_template($OUTPUT)
        );
        break;
case 'classes':
    require_once(
        $CFG->dirroot .
        '/local/qubexa_classes/lib.php'
    );

    $content =
        local_qubexa_classes_render_workspace_page();
    break;
    case 'lessons':
        if (!\core_component::get_component_directory('local_qubexa_students')) {
            $content = \local_qubexa\workspace::placeholder($page);
        } else {
            require_once(
                $CFG->dirroot . '/local/qubexa_students/lib.php'
            );

            $content = local_qubexa_students_render_workspace_page();
        }
        break;

    case 'calendar':
        if (!\core_component::get_component_directory(
            'local_qubexa_calendar'
        )) {
            $content = \local_qubexa\workspace::placeholder(
                $page
            );
        } else {
            require_once(
                $CFG->dirroot .
                '/local/qubexa_calendar/lib.php'
            );

            $content =
                local_qubexa_calendar_render_workspace_page();
        }
        break;

    default:
        $content = \local_qubexa\workspace::placeholder($page);
}

$appcontext = array_merge(
    \local_qubexa\workspace::context(
        $page,
        $title,
        $subtitle
    ),
    [
        'content' => $content,
    ]
);

echo $OUTPUT->header();

echo $OUTPUT->render_from_template(
    'local_qubexa/workspace',
    $appcontext
);

echo $OUTPUT->footer();
