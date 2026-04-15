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
 * local_recertify data generator.
 *
 * @package   local_recertify
 * @copyright  2023 Synergy Learning
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * PHPUnit data generator for local_recertify.
 */
class local_recertify_generator extends testing_module_generator {
    /**
     * Function to create a dummy course completion record.
     * For use with a Moodle site only.
     *
     * @param array|stdClass $record
     * @return completion_completion the completion object
     */
    public function create_course_completion($record = null): completion_completion {
        global $DB;

        $record = (array)$record;

        if (empty($record['userid'])) {
            throw new coding_exception('username must be present in phpunit_util::create_completion() $record');
        }

        if (empty($record['courseid'])) {
            throw new coding_exception('course must be present in phpunit_util::create_completion() $record');
        }

        $userid = $record['userid'];
        $courseid = $record['courseid'];
        $timestamp = $record['timestamp'] ?? null;

        if (!$DB->get_record('course_completions', ['userid' => $userid, 'course' => $courseid])) {
            $comp = new completion_completion(['userid' => $userid, 'course' => $courseid]);
            switch ($record['complete'] ?? '') {
                case 'complete':
                    $comp->mark_complete($timestamp);
                    break;
                case 'enrolled':
                    $comp->mark_enrolled($timestamp);
                    break;
                case 'inprogress':
                    $comp->mark_inprogress($timestamp);
                    break;
                default:
                    $comp->mark_complete($timestamp);
            }
        }

        return $comp;
    }
}
