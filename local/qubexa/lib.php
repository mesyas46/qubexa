<?php
defined('MOODLE_INTERNAL') || die();

function local_qubexa_extend_navigation(global_navigation $navigation): void {
    if (!isloggedin() || isguestuser()) { return; }
    $context = context_system::instance();
    if (!has_capability('local/qubexa:view', $context)) { return; }
    $navigation->add_node(navigation_node::create(
        get_string('workspace', 'local_qubexa'),
        new moodle_url('/local/qubexa/index.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'local_qubexa_workspace',
        new pix_icon('i/dashboard', '')
    ));
}

/** Shared Qubexa application sidebar. */
function local_qubexa_workspace_sidebar(string $active = 'home'): string {
    $items = [
        'home' => ['Ana Sayfa', '/local/qubexa/index.php', '⌂'],
        'students' => ['Öğrencilerim', '/local/qubexa_students/index.php', '♙'],
        'groups' => ['Ders Gruplarım', '#', '◉'],
        'knowledge' => ['Bilgi Merkezi', '#', '▣'],
        'calendar' => ['Takvim', '/calendar/view.php', '□'],
        'assessment' => ['Ölçme ve Değerlendirme', '#', '✓'],
        'office' => ['Özel Ders Ofisim', '#', '₺'],
        'promotion' => ['Ders Tanıtımlarım', '#', '★'],
        'reports' => ['Raporlar', '#', '▥'],
    ];

    $html = html_writer::start_tag('aside', ['class' => 'qubexa-sidebar']);
    $html .= html_writer::start_div('qubexa-brand');
    $html .= html_writer::tag('div', 'Q', ['class' => 'qubexa-mark']);
    $html .= html_writer::start_div();
    $html .= html_writer::tag('strong', 'QUBEXA');
    $html .= html_writer::tag('span', 'Öğretmenin Dijital Ofisi');
    $html .= html_writer::end_div() . html_writer::end_div();
    $html .= html_writer::start_tag('nav', ['class' => 'qubexa-nav', 'aria-label' => 'Qubexa']);
    foreach ($items as $key => [$label, $path, $icon]) {
        $classes = 'qubexa-nav-item' . ($active === $key ? ' is-active' : '');
        $url = $path === '#' ? '#' : (new moodle_url($path))->out(false);
        $html .= html_writer::start_tag('a', ['class' => $classes, 'href' => $url]);
        $html .= html_writer::tag('span', $icon, ['class' => 'qubexa-nav-icon', 'aria-hidden' => 'true']);
        $html .= html_writer::tag('span', $label);
        $html .= html_writer::end_tag('a');
    }
    $html .= html_writer::end_tag('nav') . html_writer::end_tag('aside');
    return $html;
}

function local_qubexa_workspace_open(string $active = 'home'): string {
    return html_writer::start_div('qubexa-shell') . local_qubexa_workspace_sidebar($active)
        . html_writer::start_tag('main', ['class' => 'qubexa-main']);
}

function local_qubexa_workspace_close(): string {
    return html_writer::end_tag('main') . html_writer::end_div();
}
