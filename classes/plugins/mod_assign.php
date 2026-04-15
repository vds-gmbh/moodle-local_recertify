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
 * Assign handler event.
 *
 * @package     local_recertify
 * @copyright  Catalyst IT
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recertify\plugins;

use lang_string;

/**
 * Quiz handler event.
 *
 * @package    local_recertify
 * @copyright  Catalyst IT
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class mod_assign {
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
            'assign',
            '',
            get_string('donothing', 'local_recertify'),
            LOCAL_RECERTIFY_NOTHING
        );
        $cba[] = $mform->createElement(
            'radio',
            'assign',
            '',
            get_string('extraattempt', 'local_recertify'),
            LOCAL_RECERTIFY_EXTRAATTEMPT
        );
        $cba[] = $mform->createElement('checkbox', 'assignevent', '', get_string('assignevent', 'local_recertify'));
        $mform->addGroup($cba, 'assign', get_string('assignattempts', 'local_recertify'), [' '], false);
        $mform->addHelpButton('assign', 'assignattempts', 'local_recertify');

        $mform->setDefault('assign', $config->assignattempts);
        $mform->setDefault('assignevent', $config->assignevent);

        $mform->disabledIf('assign', 'enable', 'notchecked');
    }

    /**
     * Add sitelevel settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings($settings) {
        $choices = [LOCAL_RECERTIFY_NOTHING => new lang_string('donothing', 'local_recertify'),
            LOCAL_RECERTIFY_EXTRAATTEMPT => new lang_string('extraattempt', 'local_recertify')];

        $settings->add(new \admin_setting_configselect(
            'local_recertify/assignattempts',
            new lang_string('assignattempts', 'local_recertify'),
            new lang_string('assignattempts_help', 'local_recertify'),
            LOCAL_RECERTIFY_NOTHING,
            $choices
        ));

        $settings->add(new \admin_setting_configcheckbox(
            'local_recertify/assignevent',
            new lang_string('assignevent', 'local_recertify'),
            '',
            0
        ));
    }

    /**
     * Reset assign records.
     * @param \int $userid - record with user information for recertify
     * @param \stdClass $course - course record.
     * @param \stdClass $config - recertify config.
     */
    public static function reset($userid, $course, $config) {
        global $DB;
        if (empty($config->assign)) {
            return '';
        } else if ($config->assign == LOCAL_RECERTIFY_EXTRAATTEMPT) {
            $sql = "SELECT DISTINCT a.*
                      FROM {assign} a
                      JOIN {assign_submission} s ON a.id = s.assignment
                     WHERE a.course = ? AND s.userid = ?";
            $assigns = $DB->get_recordset_sql($sql, [$course->id, $userid]);
            $nopermissions = false;
            foreach ($assigns as $assign) {
                $cm = get_coursemodule_from_instance('assign', $assign->id);
                $context = \context_module::instance($cm->id);
                if (has_capability('mod/assign:grade', $context)) {
                    // Assign add_attempt() is protected - use reflection so we don't have to write our own.
                    $r = new \ReflectionMethod('assign', 'add_attempt');
                    $r->setAccessible(true);
                    $r->invoke(new \assign($context, $cm, $course), $userid);
                } else {
                    $nopermissions = true;
                }
            }
            if ($nopermissions) {
                return get_string('noassigngradepermission', 'local_recertify');
            }
        }
        return '';
    }
}
