<?php
defined('MOODLE_INTERNAL') || die();
$capabilities = [
 'local/qubexa:view' => [
  'captype'=>'read', 'contextlevel'=>CONTEXT_SYSTEM,
  'archetypes'=>['manager'=>CAP_ALLOW,'coursecreator'=>CAP_ALLOW,'editingteacher'=>CAP_ALLOW,'teacher'=>CAP_ALLOW],
 ],
];
