<?php
defined('MOODLE_INTERNAL') || die();
$capabilities=[
 'local/qubexa_students:view'=>['captype'=>'read','contextlevel'=>CONTEXT_SYSTEM,'archetypes'=>['manager'=>CAP_ALLOW,'coursecreator'=>CAP_ALLOW,'editingteacher'=>CAP_ALLOW,'teacher'=>CAP_ALLOW]],
 'local/qubexa_students:manage'=>['captype'=>'write','riskbitmask'=>RISK_PERSONAL,'contextlevel'=>CONTEXT_SYSTEM,'archetypes'=>['manager'=>CAP_ALLOW,'coursecreator'=>CAP_ALLOW,'editingteacher'=>CAP_ALLOW,'teacher'=>CAP_ALLOW]],
];
