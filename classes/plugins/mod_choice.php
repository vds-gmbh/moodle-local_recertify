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
 * Choice handler event.
 *
 * @package     local_recertify
 * @copyright  Antonio Duran
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recertify\plugins;

use lang_string;

/**
 * Choice handler event.
 *
 * @package    local_recertify
 * @copyright  Antonio Duran
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class mod_choice {
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
            'choice',
            '',
            get_string('donothing', 'local_recertify'),
            LOCAL_RECERTIFY_NOTHING
        );
        $cba[] = $mform->createElement(
            'radio',
            'choice',
            '',
            get_string('delete', 'local_recertify'),
            LOCAL_RECERTIFY_DELETE
        );

        $mform->addGroup($cba, 'choice', get_string('choiceattempts', 'local_recertify'), [' '], false);
        $mform->addHelpButton('choice', 'choiceattempts', 'local_recertify');
        $mform->setDefault('choice', $config->choiceattempts);

        $mform->addElement(
            'checkbox',
            'archivechoice',
            get_string('archive', 'local_recertify')
        );
        $mform->setDefault('archivechoice', $config->archivechoice);

        $mform->disabledIf('archivechoice', 'enable', 'notchecked');
        $mform->hideIf('archivechoice', 'choice', 'notchecked');
        $mform->disabledIf('choice', 'enable', 'notchecked');
    }

    /**
     * Add sitelevel settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings($settings) {

        $choices = [LOCAL_RECERTIFY_NOTHING => new lang_string('donothing', 'local_recertify'),
                         LOCAL_RECERTIFY_DELETE => new lang_string('delete', 'local_recertify')];

        $settings->add(new \admin_setting_configselect(
            'local_recertify/choiceattempts',
            new lang_string('choiceattempts', 'local_recertify'),
            new lang_string('choiceattempts_help', 'local_recertify'),
            LOCAL_RECERTIFY_NOTHING,
            $choices
        ));

        $settings->add(new \admin_setting_configcheckbox(
            'local_recertify/archivechoice',
            new lang_string('archivechoice', 'local_recertify'),
            '',
            1
        ));
    }

    /**
     * Reset and archive choice records.
     * @param \stdclass $userid - user id
     * @param \stdClass $course - course record.
     * @param \stdClass $config - recertify config.
     */
    public static function reset($userid, $course, $config) {
        global $DB;

        if (empty($config->choice)) {
            return;
        } else if ($config->choice == LOCAL_RECERTIFY_DELETE) {
            $params = ['userid' => $userid, 'course' => $course->id];
            $selectsql = 'userid = ? AND choiceid IN (SELECT id FROM {choice} WHERE course = ?)';
            if ($config->archivechoice) {
                $choiceanswers = $DB->get_records_select('choice_answers', $selectsql, $params);
                foreach ($choiceanswers as $cid => $unused) {
                    // Add courseid to records to help with restore process.
                    $choiceanswers[$cid]->course = $course->id;
                }
                $DB->insert_records('local_recertify_cha', $choiceanswers);
            }
            $DB->delete_records_select('choice_answers', $selectsql, $params);
        }
    }
}
