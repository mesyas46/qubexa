<?php
namespace local_qubexa\navigation;

defined('MOODLE_INTERNAL') || die();

use local_qubexa\application\component_registry;
use moodle_url;

/**
 * Builds the Qubexa workspace navigation.
 *
 * @package    local_qubexa
 * @copyright  2026 Qubexa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class navigation_builder {
    /**
     * Build sidebar items.
     *
     * @param string $currentpage Current workspace page.
     * @return array
     */
    public static function build(string $currentpage): array {
        $components = component_registry::get_components();
        $items = [];

        uasort($components, static function(array $first, array $second): int {
            return $first['order'] <=> $second['order'];
        });

        foreach ($components as $key => $component) {
            if (empty($component['visible'])) {
                continue;
            }

            $items[] = [
                'key' => $key,
                'label' => $component['name'],
                'icon' => $component['icon'],
                'url' => (new moodle_url(
                    '/local/qubexa/index.php',
                    ['page' => $component['page']]
                ))->out(false),
                'active' => $component['page'] === $currentpage,
                'planned' => $component['status'] === 'planned',
            ];
        }

        return $items;
    }
}