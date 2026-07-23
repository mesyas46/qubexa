<?php
defined('MOODLE_INTERNAL') || die();
if ($hassiteconfig) {
    $settings = new admin_settingpage('local_qubexa', get_string('pluginname', 'local_qubexa'));
    $settings->add(new admin_setting_configtext('local_qubexa/brandname', get_string('brandname','local_qubexa'), get_string('brandnamedesc','local_qubexa'), 'QUBEXA', PARAM_TEXT));
    $ADMIN->add('localplugins', $settings);
}
