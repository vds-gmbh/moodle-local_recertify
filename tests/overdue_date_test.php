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

require_once("$CFG->dirroot/enrol/locallib.php");

/**
 * Class dialogue_test.
 *
 * @package    local_recertify
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class overdue_date_test extends \advanced_testcase {
    /**
     * Make sure the overdue date in the email calculation is correct.
     */
    public function test_local_recertify() {
        global $DB;

        $this->resetAfterTest();
        $gen = $this->getDataGenerator();
        set_config('enablecompletion', true);

        $course = $gen->create_course(['enablecompletion' => true]);
        $student = $gen->create_user(['username' => 'student']);
        $teacher = $gen->create_user(['username' => 'teacher']);
        $supervisorrole = create_role('supervisor', 'supervisor', 'supervisor', 'teacher');

        $gen->enrol_user($student->id, $course->id, 'student');
        $gen->enrol_user($teacher->id, $course->id, 'supervisor');

        $courseconfig = [];
        $courseconfig[] = ['course' => $course->id, 'name' => 'enable', 'value' => 1];
        $courseconfig[] = ['course' => $course->id, 'name' => 'recertifyduration', 'value' => 1];
        $courseconfig[] = ['course' => $course->id, 'name' => 'supervisoremailenable', 'value' => 1];
        $courseconfig[] = ['course' => $course->id, 'name' => 'archivecompletiondata', 'value' => 1];
        $courseconfig[] = ['course' => $course->id, 'name' => 'deletegradedata', 'value' => 0];
        $courseconfig[] = ['course' => $course->id, 'name' => 'recertifyemailenable', 'value' => 1];
        $courseconfig[] = ['course' => $course->id, 'name' => 'recertifyemailbody', 'value' => 'testing'];
        $courseconfig[] = ['course' => $course->id, 'name' => 'recertifyemailsubject', 'value' => 'testing'];
        $DB->insert_records('local_recertify_config', $courseconfig);
        $studentcontext = \context_user::instance($student->id);

        set_config('emailenable', 1, 'local_recertify');
        set_config('supervisorrole', $supervisorrole, 'local_recertify');
        set_config('archivecompletiondata', 1, 'local_recertify');
        role_assign($supervisorrole, $teacher->id, $studentcontext);
        // Subract 5 days from time enrolled and completion time.
        $ccompletion = new \completion_completion(['course' => $course->id,
                'userid' => $student->id,
                'timeenrolled' => time() - 432000,
                'timestarted' => time() - 432000,
        ]);
        $ccompletion->mark_complete(time() - 432000);
        $this->waitForSecond();
        $this->waitForSecond();
        $recertify = new check_recertify();
        $recertify->execute();
        $sink = $this->redirectEmails();
        $notifysupervisorstask = new supervisor_notification();
        $notifysupervisorstask->execute();
        $messages = $sink->get_messages();
        $sink->clear();
        $supervisornotification = array_shift($messages);
        // Result should be 1 as we count from time the user was reset in this scenario.
        $this->assertStringContainsString("seit 1 Tagen", $supervisornotification->body);
        $DB->get_record('user_enrolments', ['userid' => $student->id]);

        // Subtract 10 days from time enrolled.
        $gen->enrol_user($student->id, $course->id, 'student', 'manual', time() - 864000);
        $DB->delete_records('local_recertify_cc');
        $recertify = new check_recertify();
        $recertify->execute();
        $notifysupervisorstask = new supervisor_notification();
        $notifysupervisorstask->execute();
        $messages = $sink->get_messages();
        $newnotification = array_shift($messages);
        $this->assertStringContainsString("seit 11 Tagen", $newnotification->body);
        $sink->close();
    }
}
