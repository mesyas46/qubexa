<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade steps for local_qubexa_students.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_qubexa_students_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026072303) {
        $table = new xmldb_table('local_qubexa_student_notes');

        $table->add_field(
            'id',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            XMLDB_SEQUENCE,
            null
        );
        $table->add_field(
            'studentid',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'userid',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'note',
            XMLDB_TYPE_TEXT,
            null,
            null,
            XMLDB_NOTNULL,
            null,
            null
        );
        $table->add_field(
            'timecreated',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'timemodified',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );

        $table->add_key(
            'primary',
            XMLDB_KEY_PRIMARY,
            ['id']
        );
        $table->add_key(
            'studentid_fk',
            XMLDB_KEY_FOREIGN,
            ['studentid'],
            'local_qubexa_students',
            ['id']
        );
        $table->add_key(
            'userid_fk',
            XMLDB_KEY_FOREIGN,
            ['userid'],
            'user',
            ['id']
        );

        $table->add_index(
            'student_time_ix',
            XMLDB_INDEX_NOTUNIQUE,
            ['studentid', 'timecreated']
        );
        $table->add_index(
            'userid_student_ix',
            XMLDB_INDEX_NOTUNIQUE,
            ['userid', 'studentid']
        );

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(
            true,
            2026072303,
            'local',
            'qubexa_students'
        );
    }


    if ($oldversion < 2026072304) {
        $table = new xmldb_table('local_qubexa_student_exams');

        $table->add_field(
            'id',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            XMLDB_SEQUENCE,
            null
        );
        $table->add_field(
            'studentid',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'userid',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'examname',
            XMLDB_TYPE_CHAR,
            '255',
            null,
            XMLDB_NOTNULL,
            null,
            null
        );
        $table->add_field(
            'examdate',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'correctcount',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'wrongcount',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'blankcount',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'net',
            XMLDB_TYPE_NUMBER,
            '10,2',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'description',
            XMLDB_TYPE_TEXT,
            null,
            null,
            null,
            null,
            null
        );
        $table->add_field(
            'timecreated',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'timemodified',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );

        $table->add_key(
            'primary',
            XMLDB_KEY_PRIMARY,
            ['id']
        );
        $table->add_key(
            'studentid_fk',
            XMLDB_KEY_FOREIGN,
            ['studentid'],
            'local_qubexa_students',
            ['id']
        );
        $table->add_key(
            'userid_fk',
            XMLDB_KEY_FOREIGN,
            ['userid'],
            'user',
            ['id']
        );

        $table->add_index(
            'student_date_ix',
            XMLDB_INDEX_NOTUNIQUE,
            ['studentid', 'examdate']
        );
        $table->add_index(
            'userid_student_ix',
            XMLDB_INDEX_NOTUNIQUE,
            ['userid', 'studentid']
        );

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(
            true,
            2026072304,
            'local',
            'qubexa_students'
        );
    }


    if ($oldversion < 2026072305) {
        $table = new xmldb_table('local_qubexa_student_lessons');

        $table->add_field(
            'id',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            XMLDB_SEQUENCE,
            null
        );
        $table->add_field(
            'studentid',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'userid',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'lessondate',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'starttime',
            XMLDB_TYPE_CHAR,
            '5',
            null,
            XMLDB_NOTNULL,
            null,
            null
        );
        $table->add_field(
            'endtime',
            XMLDB_TYPE_CHAR,
            '5',
            null,
            XMLDB_NOTNULL,
            null,
            null
        );
        $table->add_field(
            'duration',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'topic',
            XMLDB_TYPE_CHAR,
            '255',
            null,
            XMLDB_NOTNULL,
            null,
            null
        );
        $table->add_field(
            'status',
            XMLDB_TYPE_CHAR,
            '20',
            null,
            XMLDB_NOTNULL,
            null,
            'attended'
        );
        $table->add_field(
            'homeworkgiven',
            XMLDB_TYPE_INTEGER,
            '1',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'homeworknote',
            XMLDB_TYPE_TEXT,
            null,
            null,
            null,
            null,
            null
        );
        $table->add_field(
            'teachernote',
            XMLDB_TYPE_TEXT,
            null,
            null,
            null,
            null,
            null
        );
        $table->add_field(
            'nextlesson',
            XMLDB_TYPE_TEXT,
            null,
            null,
            null,
            null,
            null
        );
        $table->add_field(
            'timecreated',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'timemodified',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );

        $table->add_key(
            'primary',
            XMLDB_KEY_PRIMARY,
            ['id']
        );
        $table->add_key(
            'studentid_fk',
            XMLDB_KEY_FOREIGN,
            ['studentid'],
            'local_qubexa_students',
            ['id']
        );
        $table->add_key(
            'userid_fk',
            XMLDB_KEY_FOREIGN,
            ['userid'],
            'user',
            ['id']
        );

        $table->add_index(
            'student_date_ix',
            XMLDB_INDEX_NOTUNIQUE,
            ['studentid', 'lessondate']
        );
        $table->add_index(
            'userid_student_ix',
            XMLDB_INDEX_NOTUNIQUE,
            ['userid', 'studentid']
        );
        $table->add_index(
            'student_status_ix',
            XMLDB_INDEX_NOTUNIQUE,
            ['studentid', 'status']
        );

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(
            true,
            2026072305,
            'local',
            'qubexa_students'
        );
    }


    if ($oldversion < 2026072306) {
        $table = new xmldb_table('local_qubexa_student_payments');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('paymentdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('amount', XMLDB_TYPE_NUMBER, '12,2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'paid');
        $table->add_field('method', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'cash');
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('studentid_fk', XMLDB_KEY_FOREIGN, ['studentid'], 'local_qubexa_students', ['id']);
        $table->add_key('userid_fk', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_index('student_date_ix', XMLDB_INDEX_NOTUNIQUE, ['studentid', 'paymentdate']);
        $table->add_index('userid_student_ix', XMLDB_INDEX_NOTUNIQUE, ['userid', 'studentid']);
        $table->add_index('student_status_ix', XMLDB_INDEX_NOTUNIQUE, ['studentid', 'status']);
        if (!$dbman->table_exists($table)) { $dbman->create_table($table); }
        upgrade_plugin_savepoint(true, 2026072306, 'local', 'qubexa_students');
    }

    if ($oldversion < 2026080904) {
        $table = new xmldb_table('local_qubexa_homeworks');

        $table->add_field(
            'id',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            XMLDB_SEQUENCE,
            null
        );
        $table->add_field(
            'studentid',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'userid',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'title',
            XMLDB_TYPE_CHAR,
            '255',
            null,
            XMLDB_NOTNULL,
            null,
            null
        );
        $table->add_field(
            'duedate',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'status',
            XMLDB_TYPE_CHAR,
            '20',
            null,
            XMLDB_NOTNULL,
            null,
            'pending'
        );
        $table->add_field(
            'description',
            XMLDB_TYPE_TEXT,
            null,
            null,
            null,
            null,
            null
        );
        $table->add_field(
            'timecreated',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        $table->add_field(
            'timemodified',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );

        $table->add_key(
            'primary',
            XMLDB_KEY_PRIMARY,
            ['id']
        );
        $table->add_key(
            'studentid_fk',
            XMLDB_KEY_FOREIGN,
            ['studentid'],
            'local_qubexa_students',
            ['id']
        );
        $table->add_key(
            'userid_fk',
            XMLDB_KEY_FOREIGN,
            ['userid'],
            'user',
            ['id']
        );

        $table->add_index(
            'student_due_ix',
            XMLDB_INDEX_NOTUNIQUE,
            ['studentid', 'duedate']
        );
        $table->add_index(
            'userid_student_ix',
            XMLDB_INDEX_NOTUNIQUE,
            ['userid', 'studentid']
        );
        $table->add_index(
            'student_status_ix',
            XMLDB_INDEX_NOTUNIQUE,
            ['studentid', 'status']
        );

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(
            true,
            2026080904,
            'local',
            'qubexa_students'
        );
    }

    return true;
}
