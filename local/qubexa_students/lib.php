<?php
defined('MOODLE_INTERNAL') || die();

function local_qubexa_students_extend_navigation(
    global_navigation $navigation
): void {
    // Qubexa Framework owns application navigation.
}

function local_qubexa_students_render_workspace_page(): string {
    global $OUTPUT, $PAGE;

    $context = context_system::instance();
    require_capability('local/qubexa_students:view', $context);

    // Existing list/form styles.
    $PAGE->requires->css(
        new moodle_url('/local/qubexa_students/styles.css')
    );

    // Student Panel v2 styles.
    $PAGE->requires->css(
        new moodle_url(
            '/local/qubexa_students/student_panel_v2.css'
        )
    );
    $PAGE->requires->css(
        new moodle_url(
            '/local/qubexa_students/student_lessons.css'
        )
    );
    $PAGE->requires->css(
        new moodle_url(
            '/local/qubexa_students/student_experience.css'
        )
    );
    $PAGE->requires->css(
        new moodle_url(
            '/local/qubexa_students/student_timeline.css'
        )
    );
    $PAGE->requires->css(
        new moodle_url(
            '/local/qubexa_students/student_panel_v3.css'
        )
    );
    $PAGE->requires->css(new moodle_url('/local/qubexa_students/student_payments.css'));
    $PAGE->requires->css(
        new moodle_url(
            '/local/qubexa_students/student_homeworks.css'
        )
    );

    // Load modules in dependency order.
    $PAGE->requires->js(
        new moodle_url(
            '/local/qubexa_students/student_panel.js'
        )
    );
    $PAGE->requires->js(
        new moodle_url(
            '/local/qubexa_students/student_tabs.js'
        )
    );
    $PAGE->requires->js(
        new moodle_url(
            '/local/qubexa_students/student_notes.js'
        )
    );
    $PAGE->requires->js(
        new moodle_url(
            '/local/qubexa_students/student_exams.js'
        )
    );
    $PAGE->requires->js(
        new moodle_url(
            '/local/qubexa_students/student_lessons.js'
        )
    );
    $PAGE->requires->js(
        new moodle_url(
            '/local/qubexa_students/student_progress.js'
        )
    );
    $PAGE->requires->js(
        new moodle_url(
            '/local/qubexa_students/student_timeline.js'
        )
    );
    $PAGE->requires->js(new moodle_url('/local/qubexa_students/student_payments.js'));
    $PAGE->requires->js(
        new moodle_url(
            '/local/qubexa_students/student_homeworks.js'
        )
    );

    $search = optional_param('search', '', PARAM_TEXT);
    $status = optional_param('status', '', PARAM_ALPHA);

    $page = new \local_qubexa_students\output\students_page(
        $search,
        $status
    );

    return $OUTPUT->render_from_template(
        'local_qubexa_students/students_page',
        $page->export_for_template($OUTPUT)
    );
}
