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
 * Behat data generator for local_recertify
 *
 * @package   local_recertify
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Behat data generator for local_recertify.
 */
class behat_local_recertify_generator extends behat_generator_base {
    /**
     * Get a list of the entities that Behat can create using the generator step.
     * For use with a Moodle site only.
     *
     * @return array an array of accepted entities
     */
    protected function get_creatable_entities(): array {
        return [
            'course completions' => [
                'singular' => 'course completion',
                'datagenerator' => 'course_completion',
                'required' => [
                    'user',
                    'course',
                ],
                'switchids' => [
                    'user' => 'userid',
                    'course' => 'courseid',
                ],
            ],
        ];
    }
}
