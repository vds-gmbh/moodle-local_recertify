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
 * Email utility for local_recertify.
 *
 * @package    local_recertify
 * @copyright  2021 Philipp Steingrebe
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Provides the email wrapper template for recertification notifications.
 *
 * @package    local_recertify
 * @copyright  2021 Philipp Steingrebe
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_recertify_recertify_emails {
    /**
     * Wrap email content in the standard email template.
     *
     * The template is loaded from the language string 'useremail:template'.
     *
     * @param string $preheader The email preheader text.
     * @param string $content The HTML email body content.
     * @return string The complete HTML email.
     */
    public static function get_email(string $preheader, string $content) {
        return str_replace(
            [
                '{preheader}',
                '{content}',
            ],
            [
                $preheader,
                $content,
            ],
            get_string('useremail:template', 'local_recertify')
        );
    }
}
