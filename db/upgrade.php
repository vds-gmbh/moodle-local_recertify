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

        local_recertify_repair_orphaned_viewed();

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

        $field = new xmldb_field('gradednotificationsenttime', XMLDB_TYPE_INTEGER, '10', null, null, null, null,
            'timemodifiedoffline');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2026081002, 'local', 'recertify');
    }

    return true;
}

/**
 * Remove course_modules_viewed rows left behind by earlier resets.
 *
 * Before this release the reset deleted course_modules_completion but not course_modules_viewed.
 * completion_info::set_module_viewed() returns early when a viewed row exists, so the affected
 * users could never complete a view-tracked activity again. A viewed row without a matching
 * completion row can only be the result of that bug: completion_info::internal_set_data() always
 * writes both rows inside one transaction.
 *
 * @return int Number of orphaned rows removed.
 */
function local_recertify_repair_orphaned_viewed(): int {
    global $DB;

    $sql = "SELECT cmv.id
              FROM {course_modules_viewed} cmv
              JOIN {course_modules} cm ON cm.id = cmv.coursemoduleid
              JOIN {local_recertify_config} rc
                   ON rc.course = cm.course AND rc.name = 'enable' AND rc.value = '1'
         LEFT JOIN {course_modules_completion} cmc
                   ON cmc.coursemoduleid = cmv.coursemoduleid AND cmc.userid = cmv.userid
             WHERE cmc.id IS NULL";
    $ids = $DB->get_fieldset_sql($sql);

    if (empty($ids)) {
        return 0;
    }

    foreach (array_chunk($ids, 1000) as $chunk) {
        $DB->delete_records_list('course_modules_viewed', 'id', $chunk);
    }

    // The stale state is cached per course, so drop it for everyone.
    \cache::make('core', 'completion')->purge();

    return count($ids);
}
