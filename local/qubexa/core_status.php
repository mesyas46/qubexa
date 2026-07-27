<?php
require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/qubexa:view', $context);

$PAGE->set_url(new moodle_url('/local/qubexa/core_status.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title('Qubexa Core Durumu');
$PAGE->set_heading('Qubexa Core Durumu');

$components = \local_qubexa\application\component_registry::get_components();

echo $OUTPUT->header();

echo html_writer::tag('h1', 'Qubexa Core Durumu');
echo html_writer::tag(
    'p',
    'Qubexa Framework tarafından kayıtlı modüller ve geliştirme durumları.'
);

$table = new html_table();
$table->head = ['Modül', 'Bileşen', 'Sayfa', 'Durum'];

foreach ($components as $component) {
    $status = $component['status'] === 'active' ? 'Aktif' : 'Planlandı';

    $table->data[] = [
        s($component['name']),
        s($component['plugin']),
        s($component['page']),
        $status,
    ];
}

echo html_writer::table($table);
echo $OUTPUT->footer();
