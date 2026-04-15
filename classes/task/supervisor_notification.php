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
 * Used to check for incompleted users and notify their supervisor.
 *
 * @package    local_recertify
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recertify\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Check for incompleted users and notify their supervisor.
 *
 * @package    local_recertify
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class supervisor_notification extends \core\task\scheduled_task
{
    private static function get_password($length = 8, $add_dashes = false, $available_sets = 'luds') {
        $sets = [];

        if (strpos($available_sets, 'l') !== false) {
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        }
        if (strpos($available_sets, 'u') !== false) {
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }
        if (strpos($available_sets, 'd') !== false) {
            $sets[] = '23456789';
        }
        if (strpos($available_sets, 's') !== false) {
            $sets[] = '!@#$%&*?';
        }

        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }

        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); $i++) {
            $password .= $all[array_rand($all)];
        }

        $password = str_shuffle($password);

        if (!$add_dashes) {
            return $password;
        }

        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while (strlen($password) > $dash_len) {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }

    /**
     * Returns the name of this task.
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('supervisor_notificationtask', 'local_recertify');
    }

    /**
     * Execute task.
     */
    public function execute() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/course/lib.php');
        require_once($CFG->dirroot . '/local/recertify/locallib.php');
        require_once($CFG->libdir . '/completionlib.php');
        require_once($CFG->libdir . '/gradelib.php');
        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        require_once($CFG->dirroot . '/mod/quiz/lib.php');
        require_once($CFG->dirroot . '/user/lib.php');

        if (!\completion_info::is_enabled_for_site()) {
            return;
        }

        try {
            $coursesSql = "SELECT c.id
                FROM {course} c
                JOIN {local_recertify_config} r ON r.course = c.id AND r.name = 'enable' AND r.value = '1'
                WHERE c.enablecompletion = " . COMPLETION_ENABLED;

            // Courses with completion enabled and recertify enabled
            $courses = $DB->get_recordset_sql($coursesSql);

            foreach ($courses as $course) {
                $config = $DB->get_records_menu('local_recertify_config', ['course' => $course->id], '', 'name, value');
                $course = \get_course($course->id);

                $namefields = [];
                foreach (\core_user\fields::get_name_fields() as $field) {
                    $namefields[] = 'u.' . $field;
                }
                $namefieldsstr = implode(',', $namefields);

                $usersSql = "SELECT
                    u.id,
                    u.username,
                    $namefieldsstr,
                    u.email,
                    u.mailformat,
                    COALESCE(NULLIF(cc.timeenrolled, 0), ue.timestart) AS timeenrolled,
                    cc.timestarted,
                    cc.timecompleted,
                    ue.status AS enrol_status
                    FROM {user} u
                    JOIN {user_enrolments} ue ON ue.userid = u.id
                    JOIN {enrol} e ON e.id = ue.enrolid
                    LEFT JOIN {course_completions} cc ON cc.userid = u.id AND cc.course = e.courseid
                    WHERE e.courseid = ?
                    AND (
                      (ue.timeend = 0 AND UNIX_TIMESTAMP() > ue.timestart) OR
                      (ue.timeend > 0 AND UNIX_TIMESTAMP() BETWEEN ue.timestart AND ue.timeend)
                    )";

                // Users in the current course incompleted attempts
                $users = $DB->get_recordset_sql($usersSql, [$course->id]);

                $supervisors = [];
                foreach ($users as $user) {
                    // Find supervisors of current user
                    $userSupervisors = $this->get_user_supervisors($user->id);

                    foreach ($userSupervisors as $supervisor) {
                        if (!isset($supervisors[$supervisor->id])) {
                            $supervisors[$supervisor->id] = [
                                'supervisor' => $supervisor,
                                'users'      => [$user],
                            ];
                        } else {
                            $supervisors[$supervisor->id]['users'][] = $user;
                        }
                    }
                }

                foreach ($supervisors as $supervisor) {
                    $this->notify_supervisor(
                        $course,
                        $supervisor['supervisor'],
                        $supervisor['users'],
                        $config
                    );
                }
            }
        } catch (\Exception $error) {
            mtrace($error->getMessage());
        }
    }

    /**
     * Notify supervisor about incompleted attempts.
     * @param \stdclass $course - record from course table.
     * @param \stdclass $supervisor - record from user table.
     * @param array $users - records from user table.
     * @param \stdClass $config - recertify config.
     */
    protected function notify_supervisor($course, $supervisor, $users, $config) {
        global $DB;

        if (!isset($config['supervisoremailenable']) || $config['supervisoremailenable'] !== '1') {
            return;
        }

        $from = \core_user::get_support_user();

        $context = \context_course::instance($course->id);
        $coursename = format_string($course->fullname, true, ['context' => $context]);
        $courselink = course_get_url($course)->out();
        $strsubject = get_string('supervisoremail:subject', 'local_recertify', $coursename);
        $strheading = get_string('supervisoremail:heading', 'local_recertify');
        $strgreeting = get_string('supervisoremail:greeting', 'local_recertify');
        $strcourselink = get_string('supervisoremail:courselink', 'local_recertify');
        $strlastname = get_string('supervisoremail:lastname', 'local_recertify');
        $strfirstname = get_string('supervisoremail:firstname', 'local_recertify');
        $stremail = get_string('supervisoremail:email', 'local_recertify');
        $strstatus = get_string('supervisoremail:status', 'local_recertify');
        $strusername = get_string('useremail:username', 'local_recertify');
        $strpassword = get_string('useremail:password', 'local_recertify');

        $messagehtml = <<<HTML
            <h2>$strheading</h2>
            <p>$strgreeting $supervisor->firstname $supervisor->lastname,<br/>
            $strcourselink <a href="$courselink">$coursename</a>.</p>
            <table>
                <thead>
                    <tr>
                        <th scope="col" style="text-align:left">$strlastname</th>
                        <th scope="col" style="text-align:left">$strfirstname</th>
                        <th scope="col" style="text-align:left">$stremail</th>
                        <th scope="col" style="text-align:left">$strstatus</th>
                    </tr>
                </thead>
                <tbody>
HTML;

        // Sort users an priorise incompleted
        usort($users, function ($a, $b) {
            if (!$a->timecompleted && $b->timecompleted) {
                return -1;
            }

            if ($a->timecompleted && !$b->timecompleted) {
                return 1;
            }

            return $a->lastname <=> $b->lastname;
        });

        $now = new \DateTime('now', \core_date::get_server_timezone_object());
        $tz = \core_date::get_server_timezone_object();

        $passwordReset = false;
        $overdue = false;

        $passwordResetUsers = [];
        $overdueUsers = [];

        foreach ($users as $user) {
            $timecompleted = $user->timecompleted ? new \DateTime('@' . $user->timecompleted, $tz) : null;
            $timeenrolled  = $user->timeenrolled ? new \DateTime('@' . $user->timeenrolled, $tz) : null;
            $timestarted   = $user->timestarted ? new \DateTime('@' . $user->timestarted, $tz) : null;
            $suspended     = $user->enrol_status == 1;
            $interval = new \DateInterval('PT' . $config['recertifyduration'] . 'S');

            // Get user's last completed time from completion table.
            if (!$timecompleted) {
                $lastimecomplete = $DB->get_field_sql(
                    '
                          SELECT timecompleted
                            FROM {local_recertify_cc}
                           WHERE course = :course
                             AND userid = :userid
                        ORDER BY id DESC LIMIT 1
                        ',
                    ['course' => $course->id, 'userid' => $user->id]
                );
                if ($lastimecomplete) {
                    $timetocheck = \local_recertify\reset_log::retrieve_last_reset_time(
                        $user->id,
                        $course->id,
                        $lastimecomplete,
                        $interval,
                        $tz
                    );
                } else {
                    $timetocheck = $timeenrolled;
                }
            }

            if ($suspended) {
                $colClassName = 'text-muted';
            } else if (!$timecompleted) {
                $daysOverdue = $now->diff($timetocheck)->format("%a") + 1;
                $colClassName = $daysOverdue > 30 ? 'text-danger' : 'text-warning';
            } else {
                $validUntil = $timecompleted->add($interval)->format('d.m.Y');
                $colClassName = 'text-success';
            }

            $email = preg_match('/^random-([0-9a-z]+)@vds\.de$/i', $user->email) ? null : $user->email;

            if (!$timecompleted && $email) {
                $overdueUsers[] = $user;
            }

            switch (true) {
                case $suspended:
                    $status = get_string('supervisoremail:status:suspended', 'local_recertify');
                    break;
                case !$timecompleted && !$timestarted:
                    $status = get_string('supervisoremail:status:notstarted', 'local_recertify', $daysOverdue);
                    break;
                case !$timecompleted:
                    $status = get_string('supervisoremail:status:started', 'local_recertify', $daysOverdue);
                    break;
                case $timecompleted:
                    $status = get_string('supervisoremail:status:completed', 'local_recertify', $validUntil);
                    break;
            }

            if ($email && !$timecompleted) {
                $overdue = true;
                $status .= ' <sup>1)</sup>';
            }

            if (!$email && !$timecompleted && !$timestarted) {
                $passwordReset = true;
                $status .= ' <sup>2)</sup>';
            }

            $messagehtml .= <<<HTML
                    <tr>
                        <td>$user->lastname</td>
                        <td>$user->firstname</td>
                        <td>$email</td>
                        <td class="$colClassName">$status</td>
                    </tr>
HTML;

            // Set new password for users without email if attempt is not completed and not started
            if (!$email && !$timecompleted && !$timestarted) {
                $newPassword = self::get_password();
                $fulluser = $DB->get_record('user', ['id' => $user->id]);
                $fulluser->password = $newPassword;
                \set_user_preference('auth_forcepasswordchange', 1, $fulluser);
                \user_update_user($fulluser, true, false);

                $passwordResetUsers[] = [
                    'user'     => $user,
                    'password' => $newPassword,
                ];
            }
        }

        $messagehtml .= <<<HTML
                </tbody>
            </table>
HTML;

        if ($overdue) {
            $stritem = get_string('useremail:overdue:item', 'local_recertify');
            $messagehtml .= <<<HTML
                <p style="margin-top: 20px;"><sup>1)</sup>$stritem</p>
HTML;
        }

        if ($passwordReset) {
            $stritem = get_string('useremail:passwordreset:item', 'local_recertify');
            $strheading = get_string('useremail:passwordreset:heading', 'local_recertify');
            $strmessage = get_string(
                'useremail:passwordreset:message',
                'local_recertify',
                ['courselink' => $courselink, 'coursename' => $coursename]
            );
            $messagehtml .= <<<HTML
                <p style="margin-top: 20px;"><sup>2)</sup>$stritem.</p>
HTML;

            $messagehtml .= <<<HTML
                <h2>$strheading</h2>
                <p>$strmessage</p>
                <table>
                    <thead>
                        <tr>
                            <th scope="col" style="text-align:left">$strlastname</th>
                            <th scope="col" style="text-align:left">$strfirstname</th>
                            <th scope="col" style="text-align:left">$strusername</th>
                            <th scope="col" style="text-align:left">$strpassword</th>
                        </tr>
                    </thead>
                    <tbody>
HTML;
            foreach ($passwordResetUsers as $resetUser) {
                $user     = $resetUser['user'];
                $password = $resetUser['password'];

                $messagehtml .= <<<HTML
                        <tr>
                            <td>$user->lastname</th>
                            <td>$user->firstname</th>
                            <td><code>$user->username</code></th>
                            <td><code>$password</code></th>
                        </tr>
HTML;
            }

            $messagehtml .= <<<HTML
                    </tbody>
                </table>
HTML;
        }

        $messagetext = html_to_text($messagehtml);
        $messagehtml = \local_recertify_recertify_emails::getEmail($strsubject, $messagehtml);

        // Directly emailing recertify message rather than using messaging.
        email_to_user(
            $supervisor,
            $from,
            $strsubject,
            $messagetext,
            $messagehtml,
            true
        );

        // Send copy to admin user
        $stradminsubject = get_string('adminemail:subject', 'local_recertify');
        email_to_user(
            $from,
            $from,
            $stradminsubject . $strsubject,
            $messagetext,
            $messagehtml,
            true
        );

        // Notify all over due users
        try {
            $strusersubject = get_string('useremail:subject', 'local_recertify', $coursename);
            $strgreeting = get_string('useremail:overdue:greeting', 'local_recertify');
            $strheading = get_string('useremail:overdue:heading', 'local_recertify');
            $strmessage = get_string(
                'useremail:overdue:message',
                'local_recertify',
                ['courselink' => $courselink, 'coursename' => $coursename]
            );

            foreach ($overdueUsers as $user) {
                $messagehtml = <<<HTML
                    <h2>$strheading</h2>
                    <p>$strgreeting $user->firstname $user->lastname,<br/>
                    $strmessage</p>
    HTML;

                $messagetext = html_to_text($messagehtml);
                $messagehtml = \local_recertify_recertify_emails::getEmail($strsubject, $messagehtml);

                email_to_user(
                    $user,
                    $from,
                    $strusersubject, // subject
                    $messagetext,
                    $messagehtml,
                    true
                );
            }
        } catch (\Exception $error) {
        }
    }

    /**
     * Finds all supervisors for given user
     *
     * @param  int $userid
     * @return array
     */

    private function get_user_supervisors($userid) {
        global $DB;

        $roleid = get_config('local_recertify', 'supervisorrole') ?: 10; // For backwards compatibiilty.

        $userContext = \context_user::instance($userid);
        $sql = "SELECT ra.userid
                  FROM {role_assignments} ra
                 WHERE ra.roleid = $roleid AND ra.contextid = ?";

        $supervisorids = array_map(
            function ($data) {
                return $data->userid;
            },
            $DB->get_records_sql(
                $sql,
                [$userContext->id]
            )
        );

        return $DB->get_records_list(
            'user',
            'id',
            $supervisorids
        );
    }
}
