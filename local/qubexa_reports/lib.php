<?php
defined('MOODLE_INTERNAL') || die();

function local_qubexa_reports_extend_navigation(
    global_navigation $navigation
): void {
    // Hocadex Framework owns application navigation.
}

function local_qubexa_reports_render_workspace_page(): string {
    global $OUTPUT, $PAGE, $USER;

    $context = context_system::instance();

    require_capability(
        'local/qubexa_reports:view',
        $context
    );

    $PAGE->requires->css(
        new moodle_url('/local/qubexa_reports/styles.css')
    );

    $classid = optional_param(
        'reportclassid',
        0,
        PARAM_INT
    );
    $studentquery = optional_param(
        'reportstudent',
        '',
        PARAM_TEXT
    );
    $datefrom = optional_param(
        'reportdatefrom',
        '',
        PARAM_RAW_TRIMMED
    );
    $dateto = optional_param(
        'reportdateto',
        '',
        PARAM_RAW_TRIMMED
    );

    $studentid = optional_param(
        'reportstudentid',
        0,
        PARAM_INT
    );

    if ($studentid > 0) {
        $studentpage = new \local_qubexa_reports\output\student_report_page(
            (int) $USER->id,
            $studentid,
            $classid,
            $studentquery,
            $datefrom,
            $dateto
        );

        return $OUTPUT->render_from_template(
            'local_qubexa_reports/student_report',
            $studentpage->export_for_template($OUTPUT)
        );
    }

    $page = new \local_qubexa_reports\output\reports_page(
        (int) $USER->id,
        $classid,
        $studentquery,
        $datefrom,
        $dateto
    );

    return $OUTPUT->render_from_template(
        'local_qubexa_reports/reports_page',
        $page->export_for_template($OUTPUT)
    );
}
