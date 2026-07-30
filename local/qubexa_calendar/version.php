<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_qubexa_calendar';
$plugin->version = 2026073000;
$plugin->requires = 2024100700;

$plugin->dependencies = [
    'local_qubexa' => 2026072302,
    'local_qubexa_students' => 2026072302,
];

$plugin->maturity = MATURITY_ALPHA;
$plugin->release = '0.2.0-sprint10';