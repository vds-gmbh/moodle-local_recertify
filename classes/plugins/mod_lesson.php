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
 * Lesson handler event.
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
 * Lesson handler event.
 *
 * @package    local_recertify
 * @copyright  2023 Dmitrii Metelkin
 * @copyright  based on code by Dan Marsden, Catalyst IT
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_lesson {
    /**
     * Tables holding per user lesson data, mapped to their archive table.
     *
     * @return array
     */
    public static function get_tables(): array {
        return [
            'lesson_attempts'  => 'local_recertify_la',
            'lesson_grades'    => 'local_recertify_lg',
            'lesson_timer'     => 'local_recertify_lt',
            'lesson_branch'    => 'local_recertify_lb',
            'lesson_overrides' => 'local_recertify_lo',
        ];
    }

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
            'lesson',
            '',
            get_string('donothing', 'local_recertify'),
            LOCAL_RECERTIFY_NOTHING
        );
        $cba[] = $mform->createElement(
            'radio',
            'lesson',
            '',
            get_string('delete', 'local_recertify'),
            LOCAL_RECERTIFY_DELETE
        );

        $mform->addGroup($cba, 'lesson', get_string('lessonattempts', 'local_recertify'), [' '], false);
        $mform->addHelpButton('lesson', 'lessonattempts', 'local_recertify');
        $mform->setDefault('lesson', $config->lesson ?? LOCAL_RECERTIFY_NOTHING);

        $mform->addElement('checkbox', 'archivelesson', get_string('archive', 'local_recertify'));
        $mform->setDefault('archivelesson', $config->archivelesson ?? 1);

        $mform->disabledIf('archivelesson', 'enable', 'notchecked');
        $mform->hideIf('archivelesson', 'lesson', 'noteq', LOCAL_RECERTIFY_DELETE);
        $mform->disabledIf('lesson', 'enable', 'notchecked');
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
            'local_recertify/lesson',
            new lang_string('lessonattempts', 'local_recertify'),
            new lang_string('lessonattempts_help', 'local_recertify'),
            LOCAL_RECERTIFY_NOTHING,
            $choices
        ));

        $settings->add(new \admin_setting_configcheckbox(
            'local_recertify/archivelesson',
            new lang_string('archivelesson', 'local_recertify'),
            '',
            1
        ));
    }

    /**
     * Reset and archive lesson records.
     *
     * The completionendreached and completiontimespent rules are evaluated live against
     * lesson_timer, so these rows have to go or the activity counts as completed again
     * without the user working through the lesson a second time.
     *
     * @param int $userid - user id
     * @param \stdClass $course - course record.
     * @param \stdClass $config - recertify config.
     */
    public static function reset($userid, $course, $config) {
        global $DB;

        if (empty($config->lesson)) {
            return;
        } else if ($config->lesson == LOCAL_RECERTIFY_DELETE) {
            $tables = self::get_tables();
            $params = ['userid' => $userid, 'course' => $course->id];
            $selectsql = 'userid = :userid AND lessonid IN (SELECT id FROM {lesson} WHERE course = :course)';

            if (!empty($config->archivelesson)) {
                foreach ($tables as $originaltable => $archivetable) {
                    $records = $DB->get_records_select($originaltable, $selectsql, $params);
                    if (empty($records)) {
                        continue;
                    }
                    foreach ($records as $record) {
                        // Add courseid to records to help with restore process.
                        $record->course = $course->id;
                    }
                    $DB->insert_records($archivetable, $records);
                }
            }

            foreach (array_keys($tables) as $originaltable) {
                $DB->delete_records_select($originaltable, $selectsql, $params);
            }
        }
    }
}
