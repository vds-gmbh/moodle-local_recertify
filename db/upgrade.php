<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Upgrade code for local_recertify.
 *
 * @package    local_recertify
 * @copyright  2018 Dan Marsden
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade local_recertify plugin.
 *
 * @param int $oldversion The old version of the plugin.
 * @return bool
 */
function xmldb_local_recertify_upgrade(int $oldversion): bool {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026081001) {
        // New archive tables: course_modules_viewed, lesson and h5pactivity data.
        $newtables = [
            'local_recertify_cmv',
            'local_recertify_la',
            'local_recertify_lg',
            'local_recertify_lt',
            'local_recertify_lb',
            'local_recertify_lo',
            'local_recertify_h5p',
            'local_recertify_h5pr',
        ];
        foreach ($newtables as $tablename) {
            $table = new xmldb_table($tablename);
            if (!$dbman->table_exists($table)) {
                $dbman->install_one_table_from_xmldb_file(
                    $CFG->dirroot . '/local/recertify/db/install.xml',
                    $tablename
                );
            }
        }

        upgrade_plugin_savepoint(true, 2026081001, 'local', 'recertify');
    }

    if ($oldversion < 2026081002) {
        // These two columns were missing from the quiz attempt archive, so their values were
        // silently dropped when attempts were archived.
        $table = new xmldb_table('local_recertify_qa');

        $field = new xmldb_field('timemodifiedoffline', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timemodified');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field(
            'gradednotificationsenttime',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            null,
            null,
            null,
            'timemodifiedoffline'
        );
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2026081002, 'local', 'recertify');
    }

    return true;
}
