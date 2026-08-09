<?php
namespace local_qubexa_students\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Single source of truth for Student Panel tabs.
 *
 * @package local_qubexa_students
 */
final class student_panel_registry {
    /**
     * Return the registered tabs in display order.
     *
     * @return array
     */
    public static function tabs(): array {
        $definitions = [
            ['key' => 'general', 'label' => 'Genel'],
            ['key' => 'notes', 'label' => 'Notlar'],
            ['key' => 'exams', 'label' => 'Sınavlar'],
            ['key' => 'lessons', 'label' => 'Dersler'],
            ['key' => 'homeworks', 'label' => 'Ödevler'],
            ['key' => 'payments', 'label' => 'Ödemeler'],
            ['key' => 'progress', 'label' => 'İlerleme'],
            ['key' => 'timeline', 'label' => 'Timeline'],
        ];

        $tabs = [];

        foreach ($definitions as $index => $definition) {
            $key = $definition['key'];

            $tabs[] = [
                'key' => $key,
                'label' => $definition['label'],
                'active' => $index === 0,
                'selected' => $index === 0 ? 'true' : 'false',
                'tabid' => 'qubexa-student-tab-' . $key,
                'panelid' => 'qubexa-student-panel-' . $key,
            ];
        }

        return $tabs;
    }
}
