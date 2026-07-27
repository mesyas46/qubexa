<?php
namespace local_qubexa\workspace;

defined('MOODLE_INTERNAL') || die();

final class navigation {

    public static function build(string $active): array {
        $definitions = [
            [
                'key' => 'dashboard',
                'label' => get_string('home', 'local_qubexa'),
                'icon' => 'home',
                'section' => 'main',
            ],
            [
                'key' => 'students',
                'label' => get_string('students', 'local_qubexa'),
                'icon' => 'users',
                'section' => 'teaching',
            ],
            [
                'key' => 'groups',
                'label' => get_string('groups', 'local_qubexa'),
                'icon' => 'groups',
                'section' => 'teaching',
            ],
            [
                'key' => 'calendar',
                'label' => get_string('calendar', 'local_qubexa'),
                'icon' => 'calendar',
                'section' => 'teaching',
            ],
            [
                'key' => 'assessment',
                'label' => get_string('assessment', 'local_qubexa'),
                'icon' => 'check',
                'section' => 'teaching',
            ],
            [
                'key' => 'knowledge',
                'label' => get_string('knowledgecenter', 'local_qubexa'),
                'icon' => 'folder',
                'section' => 'content',
            ],
            [
                'key' => 'office',
                'label' => get_string('office', 'local_qubexa'),
                'icon' => 'wallet',
                'section' => 'office',
            ],
            [
                'key' => 'promotion',
                'label' => get_string('promotion', 'local_qubexa'),
                'icon' => 'megaphone',
                'section' => 'office',
            ],
            [
                'key' => 'reports',
                'label' => get_string('reports', 'local_qubexa'),
                'icon' => 'chart',
                'section' => 'analysis',
            ],
            [
                'key' => 'settings',
                'label' => get_string('settings', 'local_qubexa'),
                'icon' => 'settings',
                'section' => 'system',
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