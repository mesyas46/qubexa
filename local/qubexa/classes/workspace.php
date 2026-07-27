<?php
namespace local_qubexa;

defined('MOODLE_INTERNAL') || die();

final class workspace {
    public const DEFAULT_PAGE = 'dashboard';

    public static function allowed_pages(): array {
        return [
            'dashboard', 'students', 'groups', 'knowledge', 'calendar',
            'assessment', 'office', 'promotion', 'reports', 'settings'
        ];
    }

    public static function page_url(string $page = self::DEFAULT_PAGE, array $params = []): \moodle_url {
        return new \moodle_url('/local/qubexa/index.php', array_merge(['page' => $page], $params));
    }

    public static function menu(string $active): array {
    return \local_qubexa\workspace\navigation::build($active);
}

    public static function context(string $active, string $title, string $subtitle = ''): array {
        global $USER;
        return [
            'activepage'=>$active,
            'pagetitle'=>$title,
            'pagesubtitle'=>$subtitle,
            'firstname'=>s($USER->firstname ?: fullname($USER)),
            'date'=>userdate(time(), get_string('strftimedaydate','langconfig')),
            'menuitems'=>self::menu($active),
            'dashboardurl'=>self::page_url()->out(false),
            'logouturl'=>(new \moodle_url('/login/logout.php',['sesskey'=>sesskey()]))->out(false),
        ];
    }

    public static function placeholder(string $page): string {
        $title=get_string($page, 'local_qubexa');
        $context=[
            'icon'=>'◌',
            'title'=>$title,
            'message'=>get_string('modulecomingsoon','local_qubexa'),
        ];
        global $OUTPUT;
        return $OUTPUT->render_from_template('local_qubexa/placeholder',$context);
    }
}
