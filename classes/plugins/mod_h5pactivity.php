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
 * H5P activity handler event.
 *
 * @package    local_recertify
 * @copyright  2023 Dmitrii Metelkin
 * @copyright  based on code by Dan Marsden, Catalyst IT
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recertify\plugins;

use lang_string;

/**
 * H5P activity handler event.
 *
 * @package    local_recertify
 * @copyright  2023 Dmitrii Metelkin
 * @copyright  based on code by Dan Marsden, Catalyst IT
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_h5pactivity {
    /**
     * Add params to form.
     *
     * @param \MoodleQuickForm $mform
     */
    public static function editingform($mform): void {
        $config = get_config('local_recertify');

        $cba = [];
        $cba[] = $mform->createElement(
            'radio',
            'h5pactivity',
            '',
            get_string('donothing', 'local_recertify'),
            LOCAL_RECERTIFY_NOTHING
        );
        $cba[] = $mform->createElement(
            'radio',
            'h5pactivity',
            '',
            get_string('delete', 'local_recertify'),
            LOCAL_RECERTIFY_DELETE
        );

        $mform->addGroup($cba, 'h5pactivity', get_string('h5pactivityattempts', 'local_recertify'), [' '], false);
        $mform->addHelpButton('h5pactivity', 'h5pactivityattempts', 'local_recertify');
        $mform->setDefault('h5pactivity', $config->h5pactivity ?? LOCAL_RECERTIFY_NOTHING);

        $mform->addElement('checkbox', 'archiveh5pactivity', get_string('archive', 'local_recertify'));
        $mform->setDefault('archiveh5pactivity', $config->archiveh5pactivity ?? 1);

        $mform->disabledIf('archiveh5pactivity', 'enable', 'notchecked');
        $mform->hideIf('archiveh5pactivity', 'h5pactivity', 'noteq', LOCAL_RECERTIFY_DELETE);
        $mform->disabledIf('h5pactivity', 'enable', 'notchecked');
    }

    /**
     * Add sitelevel settings for this plugin.
     *
     * @param \admin_settingpage $settings
     */
    public static function settings($settings) {
        $choices = [
            LOCAL_RECERTIFY_NOTHING => get_string('donothing', 'local_recertify'),
            LOCAL_RECERTIFY_DELETE => get_string('delete', 'local_recertify'),
        ];

        $settings->add(new \admin_setting_configselect(
            'local_recertify/h5pactivity',
            new lang_string('h5pactivityattempts', 'local_recertify'),
            new lang_string('h5pactivityattempts_help', 'local_recertify'),
            LOCAL_RECERTIFY_NOTHING,
            $choices
        ));

        $settings->add(new \admin_setting_configcheckbox(
            'local_recertify/archiveh5pactivity',
            new lang_string('archiveh5pactivity', 'local_recertify'),
            '',
            1
        ));
    }

    /**
     * Reset and archive H5P activity records.
     *
     * H5P completion is derived from the attempt data, so leaving the attempts behind means the
     * activity counts as completed again without the user working through it a second time.
     *
     * @param int $userid - user id
     * @param \stdClass $course - course record.
     * @param \stdClass $config - recertify config.
     */
    public static function reset($userid, $course, $config) {
        global $DB;

        if (empty($config->h5pactivity)) {
            return;
        } else if ($config->h5pactivity == LOCAL_RECERTIFY_DELETE) {
            $params = ['userid' => $userid, 'course' => $course->id];
            $attemptssql = 'userid = :userid AND h5pactivityid IN (SELECT id FROM {h5pactivity} WHERE course = :course)';
            $resultssql = 'attemptid IN (SELECT id FROM {h5pactivity_attempts} WHERE ' . $attemptssql . ')';

            if (!empty($config->archiveh5pactivity)) {
                self::archive($course, $attemptssql, $resultssql, $params);
            }

            $DB->delete_records_select('h5pactivity_attempts_results', $resultssql, $params);
            $DB->delete_records_select('h5pactivity_attempts', $attemptssql, $params);
        }
    }

    /**
     * Copy the attempts and their results into the archive tables.
     *
     * The results reference their attempt by id, so the archived attempts are written first and
     * the results are repointed at the new archive ids before they are stored.
     *
     * @param \stdClass $course - course record.
     * @param string $attemptssql - select statement matching the user's attempts.
     * @param string $resultssql - select statement matching the results of those attempts.
     * @param array $params - parameters for both select statements.
     */
    protected static function archive($course, $attemptssql, $resultssql, $params): void {
        global $DB;

        $attempts = $DB->get_records_select('h5pactivity_attempts', $attemptssql, $params);
        if (empty($attempts)) {
            return;
        }

        $attemptids = array_keys($attempts);
        foreach ($attempts as $attempt) {
            // Add courseid to records to help with restore process.
            $attempt->course = $course->id;
            $attempt->originalattemptid = $attempt->id;
        }
        $DB->insert_records('local_recertify_h5p', $attempts);

        // Map the original attempt ids onto the archive ids written just now.
        [$insql, $inparams] = $DB->get_in_or_equal($attemptids, SQL_PARAMS_NAMED);
        $archived = $DB->get_records_select(
            'local_recertify_h5p',
            "originalattemptid $insql",
            $inparams,
            '',
            'id, originalattemptid'
        );
        $idmap = [];
        foreach ($archived as $archivedattempt) {
            $idmap[$archivedattempt->originalattemptid] = $archivedattempt->id;
        }

        $results = $DB->get_records_select('h5pactivity_attempts_results', $resultssql, $params);
        if (!empty($results)) {
            foreach ($results as $result) {
                // Add courseid to records to help with restore process.
                $result->course = $course->id;
                $result->attemptid = $idmap[$result->attemptid];
            }
            $DB->insert_records('local_recertify_h5pr', $results);
        }

        // Drop the mapping again so a later restore cannot clash on ids from another site.
        $DB->execute("UPDATE {local_recertify_h5p} SET originalattemptid = 0 WHERE originalattemptid $insql", $inparams);
    }
}
