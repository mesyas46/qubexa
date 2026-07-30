<?php
namespace local_qubexa\workspace;

defined('MOODLE_INTERNAL') || die();

final class navigation {

    public static function build(string $active): array {
        $definitions = [
            [
                'key' => 'dashboard',
                'label' => get_string('dashboard', 'local_qubexa'),
                'icon' => 'home',
                'section' => 'main',
            ],
            [
                'key' => 'classes',
                'label' => get_string('classes', 'local_qubexa'),
                'icon' => 'groups',
                'section' => 'teaching',
            ],
            [
                'key' => 'lessons',
                'label' => get_string('lessons', 'local_qubexa'),
                'icon' => 'users',
                'section' => 'teaching',
            ],
            [
                'key' => 'calendar',
                'label' => get_string('calendar', 'local_qubexa'),
                'icon' => 'calendar',
                'section' => 'teaching',
            ],
            [
                'key' => 'office',
                'label' => get_string('office', 'local_qubexa'),
                'icon' => 'folder',
                'section' => 'office',
            ],
            [
                'key' => 'reports',
                'label' => get_string('reports', 'local_qubexa'),
                'icon' => 'chart',
                'section' => 'analysis',
            ],
        ];

        $result = [];

        foreach ($definitions as $item) {
            $item['url'] = \local_qubexa\workspace::page_url(
                $item['key']
            )->out(false);

            $item['active'] = $item['key'] === $active;

            $result[] = $item;
        }

        return $result;
    }
}
