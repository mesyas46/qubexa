<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_qubexa_classes_upgrade(
    int $oldversion
): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026073001) {
        $table = new xmldb_table(
            'local_qubexa_class_students'
        );

        $table->add_field(
            'id',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            XMLDB_SEQUENCE
        );

        $table->add_field(
            'userid',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL
        );

        $table->add_field(
            'classid',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL
        );

        $table->add_field(
            'firstname',
            XMLDB_TYPE_CHAR,
            '100',
            null,
            XMLDB_NOTNULL
        );

        $table->add_field(
            'lastname',
            XMLDB_TYPE_CHAR,
            '100',
            null,
            XMLDB_NOTNULL
        );
        $table->add_field(
            'studentnumber',
            XMLDB_TYPE_CHAR,
            '50',
            null,
            null
        );

        $table->add_field(
            'status',
            XMLDB_TYPE_INTEGER,
            '1',
            null,
            XMLDB_NOTNULL,
            null,
            '1'
        );

        $table->add_field(
            'timecreated',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL
        );

        $table->add_field(
            'timemodified',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL
        );
        $table->add_key(
            'primary',
            XMLDB_KEY_PRIMARY,
            ['id']
        );

        $table->add_key(
            'userid',
            XMLDB_KEY_FOREIGN,
            ['userid'],
            'user',
            ['id']
        );

        $table->add_key(
            'classid',
            XMLDB_KEY_FOREIGN,
            ['classid'],
            'local_qubexa_classes',
            ['id']
        );

        $table->add_index(
            'classstatus',
            XMLDB_INDEX_NOTUNIQUE,
            ['classid', 'status']
        );

        $table->add_index(
            'studentname',
            XMLDB_INDEX_NOTUNIQUE,
            ['classid', 'lastname', 'firstname']
        );

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(
            true,
            2026073001,
            'local',
            'qubexa_classes'
        );
    }
    if ($oldversion < 2026073002) {
        $table = new xmldb_table(
            'local_qubexa_class_points'
        );

        $table->add_field(
            'id',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            XMLDB_SEQUENCE
        );

        $table->add_field(
            'userid',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL
        );

        $table->add_field(
            'classid',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL
        );

        $table->add_field(
            'studentid',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL
        );

        $table->add_field(
            'pointvalue',
            XMLDB_TYPE_INTEGER,
            '2',
            null,
            XMLDB_NOTNULL
        );

        $table->add_field(
            'timecreated',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL
        );

        $table->add_key(
            'primary',
            XMLDB_KEY_PRIMARY,
            ['id']
        );

        $table->add_key(
            'userid',
            XMLDB_KEY_FOREIGN,
            ['userid'],
            'user',
            ['id']
        );

        $table->add_key(
            'classid',
            XMLDB_KEY_FOREIGN,
            ['classid'],
            'local_qubexa_classes',
            ['id']
        );

        $table->add_key(
            'studentid',
            XMLDB_KEY_FOREIGN,
            ['studentid'],
            'local_qubexa_class_students',
            ['id']
        );

        $table->add_index(
            'studenttime',
            XMLDB_INDEX_NOTUNIQUE,
            ['studentid', 'timecreated']
        );

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(
            true,
            2026073002,
            'local',
            'qubexa_classes'
        );
    }

    return true;
}
