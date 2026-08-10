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
 * SCORM handler event.
 *
 * @package     local_recertify
 * @copyright  Catalyst IT
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recertify\plugins;

use lang_string;

/**
 * SCORM handler event.
 *
 * @package    local_recertify
 * @copyright  Catalyst IT
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class mod_scorm {
    /**
     * Add params to form.
     * @param moodleform $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function editingform($mform): void {
        $config = get_config('local_recertify');

        $cba = [];
        $cba[] = $mform->createElement(
            'radio',
            'scorm',
            '',
            get_string('donothing', 'local_recertify'),
            LOCAL_RECERTIFY_NOTHING
        );
        $cba[] = $mform->createElement(
            'radio',
            'scorm',
            '',
            get_string('delete', 'local_recertify'),
            LOCAL_RECERTIFY_DELETE
        );

        $mform->addGroup($cba, 'scorm', get_string('scormattempts', 'local_recertify'), [' '], false);
        $mform->addHelpButton('scorm', 'scormattempts', 'local_recertify');
        $mform->setDefault('scorm', $config->scormattempts);

        $mform->addElement(
            'checkbox',
            'archivescorm',
            get_string('archive', 'local_recertify')
        );
        $mform->setDefault('archivescorm', $config->archivescorm);

        $mform->disabledIf('archivescorm', 'enable', 'notchecked');
        $mform->hideIf('archivescorm', 'scorm', 'notchecked');
        $mform->disabledIf('scorm', 'enable', 'notchecked');
    }

    /**
     * Add sitelevel settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings($settings) {
        $choices = [LOCAL_RECERTIFY_NOTHING => get_string('donothing', 'local_recertify'),
            LOCAL_RECERTIFY_DELETE => get_string('delete', 'local_recertify')];
        $settings->add(new \admin_setting_configselect(
            'local_recertify/scormattempts',
            new lang_string('scormattempts', 'local_recertify'),
            new lang_string('scormattempts_help', 'local_recertify'),
            LOCAL_RECERTIFY_NOTHING,
            $choices
        ));

        $settings->add(new \admin_setting_configcheckbox(
            'local_recertify/archivescorm',
            new lang_string('archivescorm', 'local_recertify'),
            '',
            1
        ));
    }

    /**
     * Reset and archive scorm records.
     *
     * Since Moodle 4.3 (MDL-77943) the scorm_scoes_track table no longer exists, the tracking data
     * is split across scorm_attempt, scorm_element and scorm_scoes_value. The archive table
     * local_recertify_sst keeps the old flat structure, so the data is joined back together here.
     *
     * @param \stdclass $userid - user id
     * @param \stdClass $course - course record.
     * @param \stdClass $config - recertify config.
     */
    public static function reset($userid, $course, $config) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/scorm/locallib.php');

        if (empty($config->scorm)) {
            return;
        } else if ($config->scorm == LOCAL_RECERTIFY_DELETE) {
            $params = ['userid' => $userid, 'courseid' => $course->id];
            $scormids = $DB->get_fieldset_select('scorm', 'id', 'course = :courseid', ['courseid' => $course->id]);
            if (empty($scormids)) {
                return;
            }

            if ($config->archivescorm) {
                $sql = "SELECT sv.id, sa.userid, sa.scormid, sv.scoid, sa.attempt,
                               se.element, sv.value, sv.timemodified
                          FROM {scorm_scoes_value} sv
                          JOIN {scorm_attempt} sa ON sa.id = sv.attemptid
                          JOIN {scorm_element} se ON se.id = sv.elementid
                         WHERE sa.userid = :userid
                               AND sa.scormid IN (SELECT id FROM {scorm} WHERE course = :courseid)";
                $scormscoestrack = $DB->get_records_sql($sql, $params);
                foreach ($scormscoestrack as $sid => $unused) {
                    // Add courseid to records to help with restore process.
                    $scormscoestrack[$sid]->course = $course->id;
                }
                $DB->insert_records('local_recertify_sst', $scormscoestrack);
            }

            foreach ($scormids as $scormid) {
                scorm_delete_tracks($scormid, null, $userid);
            }

            $selectsql = 'userid = ? AND scormid IN (SELECT id FROM {scorm} WHERE course = ?)';
            $DB->delete_records_select('scorm_aicc_session', $selectsql, ['userid' => $userid, 'course' => $course->id]);
        }
    }
}
