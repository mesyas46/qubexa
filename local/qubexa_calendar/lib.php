<?php
defined('MOODLE_INTERNAL') || die();

function local_qubexa_calendar_extend_navigation(
    global_navigation $navigation
): void {
    // Qubexa Framework owns application navigation.
}

function local_qubexa_calendar_render_workspace_page(): string {
    global $OUTPUT, $PAGE, $USER;

    $context = context_system::instance();

    require_capability(
        'local/qubexa_calendar:view',
        $context
    );

    $PAGE->requires->css(
        new moodle_url('/local/qubexa_calendar/styles.css')
    );

    $PAGE->requires->js(
        new moodle_url('/local/qubexa_calendar/calendar.js')
    );

    $viewdate = optional_param(
        'viewdate',
        time(),
        PARAM_INT
    );

    $page = new \local_qubexa_calendar\output\calendar_page(
        (int) $USER->id,
        $viewdate
    );

    return $OUTPUT->render_from_template(
        'local_qubexa_calendar/calendar_page',
        $page->export_for_template()
    );
}
