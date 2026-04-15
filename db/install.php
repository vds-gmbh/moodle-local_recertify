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
 * Install script for local_recertify.
 *
 * Migrates data from the predecessor plugins local_recompletion and
 * local_recompletionextension if they are installed.
 *
 * @package    local_recertify
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Migrate data from local_recompletion and local_recompletionextension.
 *
 * @return bool
 */
function xmldb_local_recertify_install(): bool {
    global $DB;

    $dbman = $DB->get_manager();

    // Archive table mapping: old recompletion table => new recertify table.
    // Schemas are identical, only the prefix changes.
    $tablemapping = [
        'local_recompletion_cc'        => 'local_recertify_cc',
        'local_recompletion_cc_cc'     => 'local_recertify_cc_cc',
        'local_recompletion_cmc'       => 'local_recertify_cmc',
        'local_recompletion_qa'        => 'local_recertify_qa',
        'local_recompletion_qg'        => 'local_recertify_qg',
        'local_recompletion_sst'       => 'local_recertify_sst',
        'local_recompletion_ltia'      => 'local_recertify_ltia',
        'local_recompletion_qr'        => 'local_recertify_qr',
        'local_recompletion_qr_bool'   => 'local_recertify_qr_bool',
        'local_recompletion_qr_date'   => 'local_recertify_qr_date',
        'local_recompletion_qr_m'      => 'local_recertify_qr_m',
        'local_recompletion_qr_other'  => 'local_recertify_qr_other',
        'local_recompletion_qr_rank'   => 'local_recertify_qr_rank',
        'local_recompletion_qr_single' => 'local_recertify_qr_single',
        'local_recompletion_qr_text'   => 'local_recertify_qr_text',
        'local_recompletion_cha'       => 'local_recertify_cha',
        'local_recompletion_ccert_is'  => 'local_recertify_ccert_is',
    ];

    // Check if local_recompletion was installed by looking for its first table.
    $recompletiontable = new \xmldb_table('local_recompletion_cc');
    if ($dbman->table_exists($recompletiontable)) {
        mtrace('local_recertify: detected local_recompletion, migrating data...');
        $migratedrows = migrate_table_data($DB, $dbman, $tablemapping);
        $migratedconfig = migrate_course_config($DB, $dbman);
        $migratedsettings = migrate_plugin_config($DB);
        mtrace("local_recertify: migrated {$migratedrows} archive rows, " .
            "{$migratedconfig} course config entries and {$migratedsettings} plugin settings from local_recompletion.");
    }

    // Check if local_recompletionextension was installed.
    $extensiontable = new \xmldb_table('local_recompextend_reset_log');
    if ($dbman->table_exists($extensiontable)) {
        mtrace('local_recertify: detected local_recompletionextension, migrating reset log...');
        $migratedlog = migrate_reset_log_data($DB);
        mtrace("local_recertify: migrated {$migratedlog} reset log entries from local_recompletionextension.");
    }

    return true;
}

/**
 * Migrate all archive table data from recompletion to recertify.
 *
 * @param \moodle_database $db
 * @param \database_manager $dbman
 * @param array $tablemapping
 * @return int Total rows copied across all tables.
 */
function migrate_table_data(\moodle_database $db, \database_manager $dbman, array $tablemapping): int {
    $total = 0;

    foreach ($tablemapping as $oldtable => $newtable) {
        $source = new \xmldb_table($oldtable);
        if (!$dbman->table_exists($source)) {
            continue;
        }

        $count = $db->count_records($oldtable);
        if ($count == 0) {
            continue;
        }

        // Copy data in batches to avoid memory issues on large installations.
        $records = $db->get_recordset($oldtable);
        $batch = [];
        $batchsize = 1000;
        $inserted = 0;

        foreach ($records as $record) {
            // Remove the id so the new table auto-increments.
            unset($record->id);
            $batch[] = $record;

            if (count($batch) >= $batchsize) {
                $db->insert_records($newtable, $batch);
                $inserted += count($batch);
                $batch = [];
            }
        }

        // Insert remaining records.
        if (!empty($batch)) {
            $db->insert_records($newtable, $batch);
            $inserted += count($batch);
        }

        $records->close();
        $total += $inserted;
    }

    return $total;
}

/**
 * Migrate course-level configuration from local_recompletion_config to local_recertify_config.
 *
 * Config keys containing 'recompletion' are renamed to 'recertify'.
 *
 * @param \moodle_database $db
 * @param \database_manager $dbman
 * @return int Number of config rows copied.
 */
function migrate_course_config(\moodle_database $db, \database_manager $dbman): int {
    $source = new \xmldb_table('local_recompletion_config');
    if (!$dbman->table_exists($source)) {
        return 0;
    }

    $records = $db->get_recordset('local_recompletion_config');
    $batch = [];
    $batchsize = 1000;
    $inserted = 0;

    foreach ($records as $record) {
        unset($record->id);
        // Rename config keys: recompletionduration -> recertifyduration, etc.
        $record->name = str_replace('recompletion', 'recertify', $record->name);
        $batch[] = $record;

        if (count($batch) >= $batchsize) {
            $db->insert_records('local_recertify_config', $batch);
            $inserted += count($batch);
            $batch = [];
        }
    }

    if (!empty($batch)) {
        $db->insert_records('local_recertify_config', $batch);
        $inserted += count($batch);
    }

    $records->close();

    return $inserted;
}

/**
 * Migrate plugin-level configuration from local_recompletion to local_recertify.
 *
 * This copies settings from the mdl_config_plugins table.
 *
 * @param \moodle_database $db
 * @return int Number of settings copied.
 */
function migrate_plugin_config(\moodle_database $db): int {
    $oldconfig = $db->get_records('config_plugins', ['plugin' => 'local_recompletion']);
    $copied = 0;

    foreach ($oldconfig as $setting) {
        // Only migrate if the setting does not already exist in the new plugin.
        $exists = $db->record_exists('config_plugins', [
            'plugin' => 'local_recertify',
            'name' => $setting->name,
        ]);

        if (!$exists) {
            set_config($setting->name, $setting->value, 'local_recertify');
            $copied++;
        }
    }

    return $copied;
}

/**
 * Migrate reset log data from local_recompletionextension to local_recertify.
 *
 * @param \moodle_database $db
 * @return int Number of reset log entries copied.
 */
function migrate_reset_log_data(\moodle_database $db): int {
    $count = $db->count_records('local_recompextend_reset_log');
    if ($count == 0) {
        return 0;
    }

    $records = $db->get_recordset('local_recompextend_reset_log');
    $batch = [];
    $batchsize = 1000;
    $inserted = 0;

    foreach ($records as $record) {
        unset($record->id);
        $batch[] = $record;

        if (count($batch) >= $batchsize) {
            $db->insert_records('local_recertify_reset_log', $batch);
            $inserted += count($batch);
            $batch = [];
        }
    }

    if (!empty($batch)) {
        $db->insert_records('local_recertify_reset_log', $batch);
        $inserted += count($batch);
    }

    $records->close();

    return $inserted;
}
