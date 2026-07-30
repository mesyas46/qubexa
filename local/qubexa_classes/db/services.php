<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_qubexa_classes_add_point' => [
        'classname' =>
            'local_qubexa_classes\external\add_point',

        'methodname' => 'execute',

        'description' =>
            'Adds a plus or minus to a class student.',

        'type' => 'write',

        'capabilities' =>
            'local/qubexa_classes:manage',

        'ajax' => true,
    ],
];