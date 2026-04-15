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
 * General functions for recertify plugin.
 *
 * @package    local_recertify
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This function extends the navigation with the recertify item
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass        $course     The course to object for the tool
 * @param context         $context    The context of the course
 */
function local_recertify_extend_navigation_course($navigation, $course, $context) {
    global $DB;
    $completion = new completion_info($course);
    if (!$completion->is_enabled()) {
        return;
    }

    if (has_capability('local/recertify:resetmycompletion', $context)) {
        $enabled = $DB->get_field('local_recertify_config', 'value', ['name' => 'enable', 'course' => $course->id]);
        if (!empty($enabled)) {
            $url = new moodle_url('/local/recertify/resetcompletion.php', ['id' => $course->id]);
            $name = get_string('resetmycompletion', 'local_recertify');
            $navigation->add($name, $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/settings', ''));
        }
    }

    if (has_capability('local/recertify:manage', $context)) {
        $url = new moodle_url('/local/recertify/recertify.php', ['id' => $course->id]);
        $name = get_string('pluginname', 'local_recertify');
        $navigation->add($name, $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/settings', ''));

        $url = new moodle_url('/local/recertify/participants.php', ['id' => $course->id]);
        $name = get_string('modifycompletiondates', 'local_recertify');
        $navigation->add($name, $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/settings', ''));
    }
}
