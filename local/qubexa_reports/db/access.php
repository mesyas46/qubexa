<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/qubexa_reports:view' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'user' => CAP_ALLOW,
        ],
    ],
];
