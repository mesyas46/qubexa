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
        $definitions = [
            ['key'=>'dashboard','label'=>get_string('home','local_qubexa'),'icon'=>'home','section'=>'main'],
            ['key'=>'students','label'=>get_string('students','local_qubexa'),'icon'=>'users','section'=>'teaching'],
            ['key'=>'groups','label'=>get_string('groups','local_qubexa'),'icon'=>'groups','section'=>'teaching'],
            ['key'=>'calendar','label'=>get_string('calendar','local_qubexa'),'icon'=>'calendar','section'=>'teaching'],
            ['key'=>'assessment','label'=>get_string('assessment','local_qubexa'),'icon'=>'check','section'=>'teaching'],
            ['key'=>'knowledge','label'=>get_string('knowledgecenter','local_qubexa'),'icon'=>'folder','section'=>'content'],
            ['key'=>'office','label'=>get_string('office','local_qubexa'),'icon'=>'wallet','section'=>'office'],
            ['key'=>'promotion','label'=>get_string('promotion','local_qubexa'),'icon'=>'megaphone','section'=>'office'],
            ['key'=>'reports','label'=>get_string('reports','local_qubexa'),'icon'=>'chart','section'=>'analysis'],
            ['key'=>'settings','label'=>get_string('settings','local_qubexa'),'icon'=>'settings','section'=>'system'],
        ];
        $result=[];
        foreach ($definitions as $item) {
            $item['url']=self::page_url($item['key'])->out(false);
            $item['active']=$item['key']===$active;
            $result[]=$item;
        }
        return $result;
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
