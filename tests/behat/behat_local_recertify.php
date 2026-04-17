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

use Behat\Gherkin\Node\TableNode;

/**
 * Custom behat steps
 *
 * @package   local_recertify
 * @copyright  2023 Synergy Learning
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom Behat step definitions for local_recertify.
 */
class behat_local_recertify extends behat_base {
    /**
     * Set the allowed role assignments for the specified role.
     *
     * @Given /^I add context type "(?P<context_string>(?:[^"]|\\")*)" for the "(?P<rolefullname_string>(?:[^"]|\\")*)" role$/
     * @param string $context
     * @param string $rolename
     * @return void Executes other steps
     */
    public function i_add_context_type_for_role($context, $rolename) {
        $parentnodes = get_string('users', 'admin') . ' > ' .
            get_string('permissions', 'role');

        // Go to home page.
        $this->execute('behat_general::i_am_on_homepage');

        // Navigate to Define roles page via site administration menu.
        $this->execute(
            'behat_navigation::i_navigate_to_in_site_administration',
            $parentnodes . ' > ' . get_string('defineroles', 'role')
        );

        $this->execute('behat_general::click_link', $rolename);
        $this->execute('behat_forms::press_button', 'Edit');
        $this->execute('behat_forms::i_set_the_field_to', [$context, 1]);
        $this->execute('behat_forms::press_button', get_string('savechanges'));
    }

    /**
     * Inserts course completions for a user.
     *
     * @Given /^the following course completions exist:$/
     * @param TableNode $table Rows with user and course columns.
     */
    public function the_following_course_completions_exist(TableNode $table) {
        global $DB;

        foreach ($table->getColumnsHash() as $row) {
            if (empty($row['user'])) {
                throw new coding_exception('username must be present');
            }

            if (empty($row['course'])) {
                throw new coding_exception('course must be present');
            }

            if (!$userid = $DB->get_field('user', 'id', ['username' => $row['user']])) {
                throw new coding_exception("user '{$row['user']}' not found");
            }

            if (!$courseid = $DB->get_field('course', 'id', ['shortname' => $row['course']])) {
                throw new coding_exception("course '{$row['course']}' not found");
            }

            $timestamp = $row['timestamp'] ?? null;

            if (!$DB->get_record('course_completions', ['userid' => $userid, 'course' => $courseid])) {
                $comp = new completion_completion(['userid' => $userid, 'course' => $courseid]);
                switch ($row['complete'] ?? '') {
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
        }
    }
}
