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
 * Detection of users without a real email address.
 *
 * @package    local_recertify
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recertify\local;

/**
 * Detection of users without a real email address.
 *
 * Some accounts carry a made up address because Moodle requires one. They are flagged through the
 * custom user profile field configured under local_recertify/noemailprofilefield and must never be
 * mailed. Every place that sends mail to a participant has to ask this class first.
 *
 * @package    local_recertify
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class noemail {
    /**
     * Is the user flagged as having no real email address?
     *
     * A non-empty, non-zero value in the configured profile field counts as "no email". Without a
     * configured field, or if the field does not exist, nobody is flagged.
     *
     * @param int $userid User id.
     * @return bool
     */
    public static function user_has_no_email(int $userid): bool {
        global $DB;

        $shortname = get_config('local_recertify', 'noemailprofilefield');
        if (empty($shortname)) {
            return false;
        }

        $value = $DB->get_field_sql(
            "SELECT d.data
               FROM {user_info_data} d
               JOIN {user_info_field} f ON f.id = d.fieldid
              WHERE f.shortname = :shortname AND d.userid = :userid",
            ['shortname' => $shortname, 'userid' => $userid]
        );

        return $value !== false && $value !== '' && $value !== '0';
    }
}
