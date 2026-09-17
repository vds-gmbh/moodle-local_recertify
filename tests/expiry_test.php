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

namespace local_recertify;

use local_recertify\task\check_recertify;
use local_recertify\task\supervisor_notification;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/recertify/locallib.php');

/**
 * Tests for when a recertification actually expires.
 *
 * @package    local_recertify
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_recertify\task\check_recertify
 * @covers     \local_recertify\task\supervisor_notification
 */
final class expiry_test extends \advanced_testcase {
    /**
     * Midnight at the start of today in the server timezone.
     *
     * @return int
     */
    private function start_of_today(): int {
        return (new \DateTime('today', \core_date::get_server_timezone_object()))->getTimestamp();
    }

    /**
     * Create a course with recertification enabled and one enrolled student.
     *
     * @param int $duration Recertification interval in seconds.
     * @return array The course record and the student record.
     */
    private function setup_course(int $duration): array {
        global $DB;

        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['enablecompletion' => COMPLETION_ENABLED]);
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');

        $settings = [
            'enable' => 1,
            'recertifyduration' => $duration,
            'archivecompletiondata' => 0,
            'deletegradedata' => 0,
            'recertifyemailenable' => 0,
        ];
        $rows = [];
        foreach ($settings as $name => $value) {
            $rows[] = ['course' => $course->id, 'name' => $name, 'value' => $value];
        }
        $DB->insert_records('local_recertify_config', $rows);

        return [$course, $student];
    }

    /**
     * Mark the course as completed for the student at the given time.
     *
     * @param int $courseid The course.
     * @param int $userid The user.
     * @param int $timecompleted When the course was completed.
     */
    private function complete_course(int $courseid, int $userid, int $timecompleted): void {
        $ccompletion = new \completion_completion([
            'course' => $courseid,
            'userid' => $userid,
            'timeenrolled' => $timecompleted,
            'timestarted' => $timecompleted,
        ]);
        $ccompletion->mark_complete($timecompleted);
    }

    /**
     * Validity runs to the end of the day the expiry falls on, not to the expiry timestamp.
     */
    public function test_last_valid_day_is_inclusive(): void {
        $this->resetAfterTest();

        $duration = DAYSECS * 30;
        $todaystart = $this->start_of_today();

        // Expiry just after midnight today: today is the last valid day and is still running.
        $this->assertFalse(check_recertify::is_past_last_valid_day($todaystart + MINSECS - $duration, $duration));

        // Expiry in the last second of today.
        $this->assertFalse(check_recertify::is_past_last_valid_day($todaystart + DAYSECS - 1 - $duration, $duration));

        // Expiry just before midnight, so the last valid day was yesterday.
        $this->assertTrue(check_recertify::is_past_last_valid_day($todaystart - MINSECS - $duration, $duration));
    }

    /**
     * The task must not reset while the last day of validity is still running.
     */
    public function test_task_keeps_completion_during_the_last_valid_day(): void {
        global $DB;

        $this->resetAfterTest();
        set_config('enablecompletion', 1);

        $duration = DAYSECS * 30;
        [$course, $student] = $this->setup_course($duration);

        // Expires just after midnight today, so the report still shows today as valid.
        $timecompleted = $this->start_of_today() + MINSECS - $duration;
        $this->complete_course($course->id, $student->id, $timecompleted);

        (new check_recertify())->execute();

        $this->assertEquals($timecompleted, $DB->get_field('course_completions', 'timecompleted', [
            'course' => $course->id,
            'userid' => $student->id,
        ]));
    }

    /**
     * Once that day is over the reset goes ahead as before.
     */
    public function test_task_resets_after_the_last_valid_day(): void {
        global $DB;

        $this->resetAfterTest();
        set_config('enablecompletion', 1);

        $duration = DAYSECS * 30;
        [$course, $student] = $this->setup_course($duration);

        // Expired just before midnight, so the last valid day was yesterday.
        $timecompleted = $this->start_of_today() - MINSECS - $duration;
        $this->complete_course($course->id, $student->id, $timecompleted);

        (new check_recertify())->execute();

        $this->assertFalse($DB->record_exists('course_completions', [
            'course' => $course->id,
            'userid' => $student->id,
        ]));
    }

    /**
     * The validity date in the report is rendered in the server timezone, not in UTC.
     */
    public function test_validity_date_uses_the_server_timezone(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setTimezone('Europe/Berlin', 'Europe/Berlin');
        set_config('enablecompletion', 1);

        $duration = DAYSECS * 30;
        [$course, $student] = $this->setup_course($duration);
        $DB->insert_record('local_recertify_config', (object) [
            'course' => $course->id,
            'name' => 'supervisoremailenable',
            'value' => 1,
        ]);

        $tz = \core_date::get_server_timezone_object();
        // Completed shortly after local midnight, which in UTC is still the previous day.
        $timecompleted = $this->start_of_today() + (MINSECS * 30);
        $this->complete_course($course->id, $student->id, $timecompleted);

        $expiry = $timecompleted + $duration;
        $expected = (new \DateTime('@' . $expiry))->setTimezone($tz)->format('d.m.Y');
        $utcdate = (new \DateTime('@' . $expiry))->format('d.m.Y');
        // Without a timezone offset at this hour the assertions below could not tell the two apart.
        $this->assertNotEquals($utcdate, $expected);

        $supervisor = $this->getDataGenerator()->create_user();
        $roleid = create_role('supervisor', 'supervisor', 'supervisor', 'teacher');
        set_config('supervisorrole', $roleid, 'local_recertify');
        set_config('emailenable', 1, 'local_recertify');
        role_assign($roleid, $supervisor->id, \context_user::instance($student->id));

        $sink = $this->redirectEmails();
        (new supervisor_notification())->execute();
        $messages = $sink->get_messages();
        $sink->close();

        $report = null;
        foreach ($messages as $message) {
            if ($message->to === $supervisor->email) {
                $report = quoted_printable_decode($message->body);
            }
        }

        $this->assertNotNull($report, 'No status report was sent to the supervisor.');
        $this->assertStringContainsString($expected, $report);
        $this->assertStringNotContainsString($utcdate, $report);
    }
}
