<?php
defined('MOODLE_INTERNAL') || die();

function local_qubexa_classes_extend_navigation(
    global_navigation $navigation
): void {
    // Qubexa Framework owns application navigation.
}

function local_qubexa_classes_render_workspace_page(): string {
    global $OUTPUT, $PAGE, $USER;

    $context = context_system::instance();

    require_capability(
        'local/qubexa_classes:view',
        $context
    );

    $PAGE->requires->css(
        new moodle_url('/local/qubexa_classes/styles.css')
    );
    $classid = optional_param(
        'classid',
        0,
        PARAM_INT
    );

    if ($classid) {
        $PAGE->requires->js(
            new moodle_url(
                '/local/qubexa_classes/points.js'
            )
        );
        $detailpage =
            new \local_qubexa_classes\output\class_detail_page(
                $classid,
                (int) $USER->id
            );

        return $OUTPUT->render_from_template(
            'local_qubexa_classes/class_detail_page',
            $detailpage->export_for_template($OUTPUT)
        );
    }
    $page = new \local_qubexa_classes\output\classes_page(
        (int) $USER->id
    );

    return $OUTPUT->render_from_template(
        'local_qubexa_classes/classes_page',
        $page->export_for_template($OUTPUT)
    );
}