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
 * Reset log functionality for local_recertify.
 *
 * @package    local_recertify
 * @copyright  2024 Synergy Learning (Lewis Robinson)
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recertify;

use local_recertify\event\completion_reset;

/**
 * Handles logging and retrieval of completion reset events.
 */
class reset_log {
    /**
     * Stores the reset event data.
     *
     * @param completion_reset $event
     * @return void
     */
    public static function store_reset_event_data(completion_reset $event): void {
        global $DB;
        $eventdata = [
            'userid' => $event->relateduserid,
            'courseid' => $event->courseid,
            'timecreated' => $event->timecreated,
        ];
        $DB->insert_record('local_recertify_reset_log', $eventdata);
    }

    /**
     * Get the last reset time for a user/course combination.
     *
     * @param int $userid
     * @param int $courseid
     * @param string $lasttimecomplete
     * @param \DateInterval $interval
     * @param \DateTimeZone $tz
     * @return \DateTime
     */
    public static function retrieve_last_reset_time(
        int $userid,
        int $courseid,
        string $lasttimecomplete,
        \DateInterval $interval,
        \DateTimeZone $tz
    ): \DateTime {
        global $DB;

        $lasttimereset = $DB->get_field_sql(
            "
            SELECT timecreated
              FROM {local_recertify_reset_log}
             WHERE userid = :userid
               AND courseid = :courseid
          ORDER BY timecreated DESC LIMIT 1",
            [
                'userid' => $userid,
                'courseid' => $courseid,
            ]
        );

        if (!$lasttimereset) {
            // Add the reset interval to the user's last completed time to figure out when their completion expired.
            $resettime = new \DateTime('@' . $lasttimecomplete, $tz);
            $resettime->add($interval)->format('d.m.Y');
        } else {
            $resettime = new \DateTime('@' . $lasttimereset, $tz);
        }

        return $resettime;
    }
}
