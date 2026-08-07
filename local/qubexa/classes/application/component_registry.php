<?php
namespace local_qubexa\application;

defined('MOODLE_INTERNAL') || die();

/**
 * Central registry for Qubexa workspace components.
 *
 * @package    local_qubexa
 * @copyright  2026 Qubexa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class component_registry {
    /**
     * Return all registered workspace components.
     *
     * @return array
     */
    public static function get_components(): array {
    return [
        'dashboard' => [
            'name' => 'Ana Sayfa',
            'plugin' => 'local_qubexa',
            'page' => 'dashboard',
            'icon' => '⌂',
            'order' => 10,
            'status' => 'active',
            'visible' => true,
        ],
        'students' => [
            'name' => 'Öğrencilerim',
            'plugin' => 'local_qubexa_students',
            'page' => 'students',
            'icon' => '♟',
            'order' => 20,
            'status' => 'active',
            'visible' => true,
        ],
        'groups' => [
            'name' => 'Ders Gruplarım',
            'plugin' => 'local_qubexa_groups',
            'page' => 'groups',
            'icon' => '◎',
            'order' => 30,
            'status' => 'planned',
            'visible' => true,
        ],
        'calendar' => [
            'name' => 'Takvim',
            'plugin' => 'local_qubexa_schedule',
            'page' => 'calendar',
            'icon' => '□',
            'order' => 40,
            'status' => 'planned',
            'visible' => true,
        ],
        'assessment' => [
            'name' => 'Ölçme ve Değerlendirme',
            'plugin' => 'mod_qubexaexam',
            'page' => 'assessment',
            'icon' => '✓',
            'order' => 50,
            'status' => 'active',
            'visible' => true,
        ],
        'knowledge' => [
            'name' => 'Bilgi Merkezi',
            'plugin' => 'local_qubexa_materials',
            'page' => 'knowledge',
            'icon' => '▣',
            'order' => 60,
            'status' => 'planned',
            'visible' => true,
        ],
        'finance' => [
            'name' => 'Özel Ders Ofisim',
            'plugin' => 'local_qubexa_finance',
            'page' => 'finance',
            'icon' => '₺',
            'order' => 70,
            'status' => 'planned',
            'visible' => true,
        ],
        'promotion' => [
            'name' => 'Ders Tanıtımlarım',
            'plugin' => 'local_qubexa_promotion',
            'page' => 'promotion',
            'icon' => '★',
            'order' => 80,
            'status' => 'planned',
            'visible' => true,
        ],
        'reports' => [
            'name' => 'Raporlar',
            'plugin' => 'local_qubexa_reports',
            'page' => 'reports',
            'icon' => '▥',
            'order' => 90,
            'status' => 'active',
            'visible' => true,
        ],
        'settings' => [
            'name' => 'Ayarlar',
            'plugin' => 'local_qubexa',
            'page' => 'settings',
            'icon' => '⚙',
            'order' => 100,
            'status' => 'active',
            'visible' => true,
        ],
    ];
}

    public static function get_component(string $key): ?array {
        $components = self::get_components();

        return $components[$key] ?? null;
    }

    public static function is_active(string $key): bool {
        $component = self::get_component($key);

        return $component !== null && $component['status'] === 'active';
    }
}
