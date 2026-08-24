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

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/recertify/locallib.php');

/**
 * Tests for resetting activity data.
 *
 * @package    local_recertify
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_recertify\task\check_recertify
 */
final class reset_test extends \advanced_testcase {
    /** @var \stdClass Course used by the test. */
    private $course;

    /** @var \stdClass Student used by the test. */
    private $student;

    /**
     * Create a course with completion enabled and an enrolled student.
     */
    protected function setUp(): void {
        parent::setUp();

        $this->resetAfterTest();
        set_config('enablecompletion', 1);

        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course(['enablecompletion' => COMPLETION_ENABLED]);
        $this->student = $generator->create_user();
        $generator->enrol_user($this->student->id, $this->course->id, 'student');
    }

    /**
     * Build the course configuration the reset task expects.
     *
     * @param array $overrides Values on top of the defaults.
     * @return \stdClass
     */
    private function get_config(array $overrides = []): \stdClass {
        return (object) array_merge([
            'archivecompletiondata' => 1,
            'deletegradedata' => 0,
            'recertifyemailenable' => 0,
        ], $overrides);
    }

    /**
     * Run the reset for the test student.
     *
     * @param array $overrides Configuration values on top of the defaults.
     */
    private function reset_student(array $overrides = []): void {
        $task = new check_recertify();
        $task->reset_user($this->student->id, $this->course, $this->get_config($overrides));
    }

    /**
     * Create a page activity that is completed by viewing it.
     *
     * @return \stdClass The course module record.
     */
    private function create_view_tracked_page(): \stdClass {
        $page = $this->getDataGenerator()->create_module('page', [
            'course' => $this->course->id,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionview' => COMPLETION_VIEW_REQUIRED,
        ]);

        return get_coursemodule_from_id('page', $page->cmid);
    }

    /**
     * Mark the activity as viewed by the student.
     *
     * @param \stdClass $cm The course module record.
     */
    private function view_as_student(\stdClass $cm): void {
        $cminfo = get_fast_modinfo($this->course, $this->student->id)->get_cm($cm->id);
        $completion = new \completion_info($this->course);
        $completion->set_module_viewed($cminfo, $this->student->id);
    }

    /**
     * The viewed row has to be removed together with the completion row.
     */
    public function test_reset_removes_and_archives_the_viewed_row(): void {
        global $DB;

        $cm = $this->create_view_tracked_page();
        $this->view_as_student($cm);

        $key = ['coursemoduleid' => $cm->id, 'userid' => $this->student->id];
        $this->assertTrue($DB->record_exists('course_modules_viewed', $key));
        $this->assertEquals(COMPLETION_COMPLETE, $DB->get_field('course_modules_completion', 'completionstate', $key));

        $this->reset_student();

        $this->assertFalse($DB->record_exists('course_modules_viewed', $key));
        $this->assertFalse($DB->record_exists('course_modules_completion', $key));

        // Both rows must be recoverable from the archive.
        $archived = $DB->get_record('local_recertify_cmv', $key);
        $this->assertNotFalse($archived);
        $this->assertEquals($this->course->id, $archived->course);
        $this->assertTrue($DB->record_exists('local_recertify_cmc', $key));
    }

    /**
     * A view tracked activity has to be completable again after a reset.
     *
     * This is the regression test for the reset leaving course_modules_viewed behind:
     * completion_info::set_module_viewed() returns early while that row exists, so the
     * activity could never reach the completed state a second time.
     */
    public function test_activity_completes_again_after_reset(): void {
        global $DB;

        $cm = $this->create_view_tracked_page();
        $this->view_as_student($cm);
        $this->reset_student();

        // Second cycle: the student opens the activity again.
        $this->view_as_student($cm);

        $key = ['coursemoduleid' => $cm->id, 'userid' => $this->student->id];
        $this->assertEquals(COMPLETION_COMPLETE, $DB->get_field('course_modules_completion', 'completionstate', $key));
        $this->assertTrue($DB->record_exists('course_modules_viewed', $key));
    }

    /**
     * Nothing is archived when archiving is switched off, but the rows still go.
     */
    public function test_reset_without_archiving(): void {
        global $DB;

        $cm = $this->create_view_tracked_page();
        $this->view_as_student($cm);

        $this->reset_student(['archivecompletiondata' => 0]);

        $key = ['coursemoduleid' => $cm->id, 'userid' => $this->student->id];
        $this->assertFalse($DB->record_exists('course_modules_viewed', $key));
        $this->assertEquals(0, $DB->count_records('local_recertify_cmv'));
    }

    /**
     * Put the database into the state an old release left behind: completion gone, viewed kept.
     *
     * @param \stdClass $cm The course module record.
     */
    private function break_completion(\stdClass $cm): void {
        global $DB;

        $DB->delete_records('course_modules_completion', [
            'coursemoduleid' => $cm->id,
            'userid' => $this->student->id,
        ]);
    }

    /**
     * An archived completion for the activity proves the reset was ours, so the row goes.
     */
    public function test_repair_uses_the_completion_archive_as_evidence(): void {
        global $DB;

        $cm = $this->create_view_tracked_page();
        $this->view_as_student($cm);

        // A real reset archives the completion; recreate that record, then break the state.
        $DB->insert_record('local_recertify_cmc', (object) [
            'coursemoduleid' => $cm->id,
            'userid' => $this->student->id,
            'completionstate' => COMPLETION_COMPLETE,
            'timemodified' => time() + 1,
            'course' => $this->course->id,
        ]);
        $this->break_completion($cm);

        $this->assertEquals(1, check_recertify::repair_orphaned_viewed());
        $this->assertFalse($DB->record_exists('course_modules_viewed', [
            'coursemoduleid' => $cm->id,
            'userid' => $this->student->id,
        ]));
    }

    /**
     * A logged reset also proves it, which covers resets that ran without archiving.
     */
    public function test_repair_uses_the_reset_log_as_evidence(): void {
        global $DB;

        $cm = $this->create_view_tracked_page();
        $this->view_as_student($cm);

        $DB->insert_record('local_recertify_reset_log', (object) [
            'userid' => $this->student->id,
            'courseid' => $this->course->id,
            'timecreated' => time() + 1,
        ]);
        $this->break_completion($cm);

        $this->assertEquals(1, check_recertify::repair_orphaned_viewed());
        $this->assertFalse($DB->record_exists('course_modules_viewed', [
            'coursemoduleid' => $cm->id,
            'userid' => $this->student->id,
        ]));
    }

    /**
     * Without evidence of one of our resets the row is left alone, whatever caused it.
     */
    public function test_repair_leaves_rows_without_evidence_alone(): void {
        global $DB;

        $cm = $this->create_view_tracked_page();
        $this->view_as_student($cm);
        $this->break_completion($cm);

        $this->assertEquals(0, check_recertify::repair_orphaned_viewed());
        $this->assertTrue($DB->record_exists('course_modules_viewed', [
            'coursemoduleid' => $cm->id,
            'userid' => $this->student->id,
        ]));
    }

    /**
     * A reset logged before the activity was viewed says nothing about that viewed row.
     */
    public function test_repair_ignores_a_reset_that_predates_the_view(): void {
        global $DB;

        $cm = $this->create_view_tracked_page();
        $this->view_as_student($cm);

        $DB->insert_record('local_recertify_reset_log', (object) [
            'userid' => $this->student->id,
            'courseid' => $this->course->id,
            'timecreated' => time() - HOURSECS,
        ]);
        $this->break_completion($cm);

        $this->assertEquals(0, check_recertify::repair_orphaned_viewed());
        $this->assertTrue($DB->record_exists('course_modules_viewed', [
            'coursemoduleid' => $cm->id,
            'userid' => $this->student->id,
        ]));
    }

    /**
     * A healthy completion pair must survive the repair untouched.
     */
    public function test_repair_keeps_healthy_rows(): void {
        global $DB;

        $cm = $this->create_view_tracked_page();
        $this->view_as_student($cm);

        $DB->insert_record('local_recertify_reset_log', (object) [
            'userid' => $this->student->id,
            'courseid' => $this->course->id,
            'timecreated' => time() + 1,
        ]);

        $this->assertEquals(0, check_recertify::repair_orphaned_viewed());
        $this->assertTrue($DB->record_exists('course_modules_viewed', [
            'coursemoduleid' => $cm->id,
            'userid' => $this->student->id,
        ]));
    }
}
