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

use local_recertify\plugins\mod_h5pactivity;
use local_recertify\plugins\mod_lesson;
use local_recertify\plugins\mod_quiz;
use local_recertify\plugins\mod_scorm;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/recertify/locallib.php');

/**
 * Tests for the activity module reset handlers.
 *
 * @package    local_recertify
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_recertify\plugins\mod_lesson
 * @covers     \local_recertify\plugins\mod_h5pactivity
 * @covers     \local_recertify\plugins\mod_quiz
 * @covers     \local_recertify\plugins\mod_scorm
 */
final class plugins_test extends \advanced_testcase {
    /** @var \stdClass Course used by the test. */
    private $course;

    /** @var \stdClass Student used by the test. */
    private $student;

    /**
     * Create a course with an enrolled student.
     */
    protected function setUp(): void {
        parent::setUp();

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course(['enablecompletion' => COMPLETION_ENABLED]);
        $this->student = $generator->create_user();
        $generator->enrol_user($this->student->id, $this->course->id, 'student');
    }

    /**
     * Write one row into each of the per user lesson tables.
     *
     * @param int $lessonid The lesson to attach the rows to.
     */
    private function create_lesson_data(int $lessonid): void {
        global $DB;

        $DB->insert_record('lesson_attempts', (object) [
            'lessonid' => $lessonid, 'pageid' => 1, 'userid' => $this->student->id,
            'answerid' => 1, 'retry' => 0, 'correct' => 1, 'useranswer' => 'yes', 'timeseen' => time(),
        ]);
        $DB->insert_record('lesson_grades', (object) [
            'lessonid' => $lessonid, 'userid' => $this->student->id,
            'grade' => 100.0, 'late' => 0, 'completed' => time(),
        ]);
        $DB->insert_record('lesson_timer', (object) [
            'lessonid' => $lessonid, 'userid' => $this->student->id,
            'starttime' => time(), 'lessontime' => time(), 'completed' => 1,
        ]);
        $DB->insert_record('lesson_branch', (object) [
            'lessonid' => $lessonid, 'userid' => $this->student->id, 'pageid' => 1,
            'retry' => 0, 'flag' => 0, 'timeseen' => time(), 'nextpageid' => 2,
        ]);
        $DB->insert_record('lesson_overrides', (object) [
            'lessonid' => $lessonid, 'userid' => $this->student->id, 'maxattempts' => 5,
        ]);
    }

    /**
     * Lesson data has to be archived and removed, otherwise completion is satisfied straight away.
     */
    public function test_lesson_reset_archives_and_deletes(): void {
        global $DB;

        $lesson = $this->getDataGenerator()->create_module('lesson', ['course' => $this->course->id]);
        $this->create_lesson_data($lesson->id);

        $config = (object) ['lesson' => LOCAL_RECERTIFY_DELETE, 'archivelesson' => 1];
        mod_lesson::reset($this->student->id, $this->course, $config);

        foreach (mod_lesson::get_tables() as $original => $archive) {
            $this->assertEquals(0, $DB->count_records($original, ['userid' => $this->student->id]), $original);
            $this->assertEquals(1, $DB->count_records($archive, ['userid' => $this->student->id]), $archive);
            $this->assertEquals($this->course->id, $DB->get_field($archive, 'course', ['userid' => $this->student->id]));
        }
    }

    /**
     * Without archiving the rows are still removed, but nothing is copied.
     */
    public function test_lesson_reset_without_archiving(): void {
        global $DB;

        $lesson = $this->getDataGenerator()->create_module('lesson', ['course' => $this->course->id]);
        $this->create_lesson_data($lesson->id);

        $config = (object) ['lesson' => LOCAL_RECERTIFY_DELETE, 'archivelesson' => 0];
        mod_lesson::reset($this->student->id, $this->course, $config);

        foreach (mod_lesson::get_tables() as $original => $archive) {
            $this->assertEquals(0, $DB->count_records($original, ['userid' => $this->student->id]), $original);
            $this->assertEquals(0, $DB->count_records($archive), $archive);
        }
    }

    /**
     * Nothing happens while the handler is switched off for the course.
     */
    public function test_lesson_reset_disabled(): void {
        global $DB;

        $lesson = $this->getDataGenerator()->create_module('lesson', ['course' => $this->course->id]);
        $this->create_lesson_data($lesson->id);

        mod_lesson::reset($this->student->id, $this->course, (object) ['lesson' => LOCAL_RECERTIFY_NOTHING]);

        $this->assertEquals(1, $DB->count_records('lesson_timer', ['userid' => $this->student->id]));
    }

    /**
     * H5P attempts and their results have to be archived and removed.
     */
    public function test_h5pactivity_reset_archives_and_deletes(): void {
        global $DB;

        $activity = $this->getDataGenerator()->create_module('h5pactivity', ['course' => $this->course->id]);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_h5pactivity');
        $generator->create_attempt([
            'h5pactivityid' => $activity->id,
            'userid' => $this->student->id,
            'attempt' => 1,
            'interactiontype' => 'compound',
            'rawscore' => 3,
            'maxscore' => 5,
        ]);

        $attemptid = $DB->get_field('h5pactivity_attempts', 'id', ['userid' => $this->student->id]);
        $this->assertNotFalse($attemptid);
        $resultcount = $DB->count_records('h5pactivity_attempts_results', ['attemptid' => $attemptid]);
        $this->assertGreaterThan(0, $resultcount);

        $config = (object) ['h5pactivity' => LOCAL_RECERTIFY_DELETE, 'archiveh5pactivity' => 1];
        mod_h5pactivity::reset($this->student->id, $this->course, $config);

        $this->assertEquals(0, $DB->count_records('h5pactivity_attempts', ['userid' => $this->student->id]));
        $this->assertEquals(0, $DB->count_records('h5pactivity_attempts_results', ['attemptid' => $attemptid]));

        $archived = $DB->get_record('local_recertify_h5p', ['userid' => $this->student->id]);
        $this->assertNotFalse($archived);
        $this->assertEquals($this->course->id, $archived->course);
        // The mapping column is cleared again so a restore cannot clash on ids from another site.
        $this->assertEquals(0, $archived->originalattemptid);

        // The archived results must point at the archived attempt, not at the deleted original.
        $this->assertEquals($resultcount, $DB->count_records('local_recertify_h5pr', ['attemptid' => $archived->id]));
    }

    /**
     * Without archiving the H5P rows are removed and nothing is copied.
     */
    public function test_h5pactivity_reset_without_archiving(): void {
        global $DB;

        $activity = $this->getDataGenerator()->create_module('h5pactivity', ['course' => $this->course->id]);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_h5pactivity');
        $generator->create_attempt([
            'h5pactivityid' => $activity->id,
            'userid' => $this->student->id,
            'attempt' => 1,
            'interactiontype' => 'compound',
        ]);

        $config = (object) ['h5pactivity' => LOCAL_RECERTIFY_DELETE, 'archiveh5pactivity' => 0];
        mod_h5pactivity::reset($this->student->id, $this->course, $config);

        $this->assertEquals(0, $DB->count_records('h5pactivity_attempts', ['userid' => $this->student->id]));
        $this->assertEquals(0, $DB->count_records('local_recertify_h5p'));
        $this->assertEquals(0, $DB->count_records('local_recertify_h5pr'));
    }

    /**
     * Quiz overrides stay in place unless the course explicitly asks for them to be reset.
     */
    public function test_quiz_overrides_kept_by_default(): void {
        global $DB;

        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $this->course->id, 'attempts' => 2]);
        $DB->insert_record('quiz_overrides', (object) [
            'quiz' => $quiz->id, 'userid' => $this->student->id, 'attempts' => 4,
        ]);

        $config = (object) ['quiz' => LOCAL_RECERTIFY_DELETE, 'archivequiz' => 0];
        mod_quiz::reset($this->student->id, $this->course, $config);

        $this->assertEquals(1, $DB->count_records('quiz_overrides', ['userid' => $this->student->id]));
    }

    /**
     * With the setting enabled the overrides are cleared, so extra attempts stop accumulating.
     */
    public function test_quiz_overrides_reset_when_enabled(): void {
        global $DB;

        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $this->course->id, 'attempts' => 2]);
        $DB->insert_record('quiz_overrides', (object) [
            'quiz' => $quiz->id, 'userid' => $this->student->id, 'attempts' => 4,
        ]);

        $config = (object) ['quiz' => LOCAL_RECERTIFY_DELETE, 'archivequiz' => 0, 'resetquizoverride' => 1];
        mod_quiz::reset($this->student->id, $this->course, $config);

        $this->assertEquals(0, $DB->count_records('quiz_overrides', ['userid' => $this->student->id]));
    }

    /**
     * SCORM tracking data lives in scorm_attempt/scorm_element/scorm_scoes_value since Moodle 4.3.
     *
     * The archive keeps the old flat shape, so the three tables have to be joined back together.
     */
    public function test_scorm_reset_archives_from_current_schema(): void {
        global $DB;

        $scorm = $this->getDataGenerator()->create_module('scorm', ['course' => $this->course->id]);
        $sco = $DB->get_record('scorm_scoes', ['scorm' => $scorm->id], '*', IGNORE_MULTIPLE);
        $this->assertNotFalse($sco);

        $attemptid = $DB->insert_record('scorm_attempt', [
            'userid' => $this->student->id, 'scormid' => $scorm->id, 'attempt' => 1,
        ]);
        $elementid = $DB->insert_record('scorm_element', ['element' => 'cmi.core.lesson_status']);
        $DB->insert_record('scorm_scoes_value', [
            'scoid' => $sco->id, 'attemptid' => $attemptid, 'elementid' => $elementid,
            'value' => 'completed', 'timemodified' => time(),
        ]);

        $config = (object) ['scorm' => LOCAL_RECERTIFY_DELETE, 'archivescorm' => 1];
        mod_scorm::reset($this->student->id, $this->course, $config);

        $this->assertEquals(0, $DB->count_records('scorm_scoes_value', ['attemptid' => $attemptid]));
        $this->assertEquals(0, $DB->count_records('scorm_attempt', ['userid' => $this->student->id]));

        $archived = $DB->get_record('local_recertify_sst', ['userid' => $this->student->id]);
        $this->assertNotFalse($archived);
        $this->assertEquals('cmi.core.lesson_status', $archived->element);
        $this->assertEquals('completed', $archived->value);
        $this->assertEquals($scorm->id, $archived->scormid);
        $this->assertEquals($sco->id, $archived->scoid);
        $this->assertEquals(1, $archived->attempt);
        $this->assertEquals($this->course->id, $archived->course);

        // scorm_element is a shared lookup table and must survive.
        $this->assertTrue($DB->record_exists('scorm_element', ['id' => $elementid]));
    }

    /**
     * A course without any SCORM activity must not blow up.
     */
    public function test_scorm_reset_without_scorm_activity(): void {
        $config = (object) ['scorm' => LOCAL_RECERTIFY_DELETE, 'archivescorm' => 1];
        mod_scorm::reset($this->student->id, $this->course, $config);

        $this->assertDebuggingNotCalled();
    }

    /**
     * The new handlers have to be picked up by the directory scan, otherwise neither the course
     * form nor the reset task ever calls them.
     */
    public function test_new_handlers_are_discovered(): void {
        $plugins = local_recertify_get_supported_plugins();

        $this->assertContains('mod_lesson', $plugins);
        $this->assertContains('mod_h5pactivity', $plugins);
    }
}
