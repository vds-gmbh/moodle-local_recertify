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
 * Defines restore_local_recertify class.
 *
 * @package     local_recertify
 * @copyright  2018 Catalyst IT
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restore plugin class.
 *
 * @package    local_recertify
 * @copyright  2018 Catalyst IT
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_local_recertify_plugin extends restore_local_plugin {
    /**
     * Returns the paths to be handled by the plugin at course level.
     */
    protected function define_course_plugin_structure() {
        $paths = [];

        // Element names are prefixed with "recertify_" to stay unique across all local
        // plugins in a course backup (see the matching note in the backup class). These
        // paths must mirror those element names exactly.
        $elepath = $this->get_pathfor('/');
        $paths[] = new restore_path_element('recertify', $elepath . '/recertify_config');
        $paths[] = new restore_path_element('recertify_cc', $elepath . '/recertify_course_completion/recertify_coursecompletion');
        $paths[] = new restore_path_element(
            'recertify_cc_cc',
            $elepath . '/recertify_course_completion/recertify_cc_crit_completions/recertify_cc_crit_compl'
        );
        $paths[] = new restore_path_element(
            'recertify_completion',
            $elepath . '/recertify_course_completion/recertify_completions/recertify_completion'
        );
        $paths[] = new restore_path_element('recertify_qa', $elepath . '/recertify_quizattempts/recertify_attempt');
        $paths[] = new restore_path_element('recertify_qg', $elepath . '/recertify_quizgrades/recertify_grade');
        $paths[] = new restore_path_element('recertify_sst', $elepath . '/recertify_scormtracks/recertify_sco_track');
        $paths[] = new restore_path_element('recertify_cha', $elepath . '/recertify_choiceanswers/recertify_choiceanswer');
        $paths[] = new restore_path_element('recertify_cmv', $elepath . '/recertify_moduleviews/recertify_moduleview');
        $paths[] = new restore_path_element('recertify_h5p', $elepath . '/recertify_h5ps/recertify_h5p');
        $paths[] = new restore_path_element(
            'recertify_h5pr',
            $elepath . '/recertify_h5ps/recertify_h5p/recertify_h5presults/recertify_h5presult'
        );
        $paths[] = new restore_path_element('recertify_la', $elepath . '/recertify_lessonattempts/recertify_lessonattempt');
        $paths[] = new restore_path_element('recertify_lg', $elepath . '/recertify_lessongrades/recertify_lessongrade');
        $paths[] = new restore_path_element('recertify_lt', $elepath . '/recertify_lessontimers/recertify_lessontimer');
        $paths[] = new restore_path_element('recertify_lb', $elepath . '/recertify_lessonbranches/recertify_lessonbranch');
        $paths[] = new restore_path_element('recertify_lo', $elepath . '/recertify_lessonoverrides/recertify_lessonoverride');

        return $paths;
    }

    /**
     * Process local_recertify table.
     * @param stdClass $data
     */
    public function process_recertify($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();

        $DB->insert_record('local_recertify_config', $data);
    }

    /**
     * Process local_recertify_cc table.
     * @param stdClass $data
     */
    public function process_recertify_cc($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recertify_cc', $data);
    }

    /**
     * Process course_completion_crit_compl table.
     * @param stdClass $data
     */
    public function process_recertify_cc_cc($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->criteriaid = $this->get_mappingid('course_completion_criteria', $data->criteriaid);

        $DB->insert_record('local_recertify_cc_cc', $data);
    }

    /**
     * Process local_recertify_cmc table.
     * @param stdClass $data
     */
    public function process_recertify_completion($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recertify_cmc', $data);
    }

    /**
     * Process local_recertify_qa table.
     * @param stdClass $data
     */
    public function process_recertify_qa($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recertify_qa', $data);
    }

    /**
     * Process local_recertify_qg table.
     * @param stdClass $data
     */
    public function process_recertify_qg($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recertify_qg', $data);
    }

    /**
     * Process local_recertify_sst table.
     * @param stdClass $data
     */
    public function process_recertify_sst($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recertify_sst', $data);
    }

    /**
     * Process local_recertify_cha table.
     * @param stdClass $data
     */
    public function process_recertify_cha($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recertify_cha', $data);
    }


    /**
     * Process local_recertify_cmv table.
     * @param stdClass $data
     */
    public function process_recertify_cmv($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recertify_cmv', $data);
    }

    /**
     * Process local_recertify_la table.
     * @param stdClass $data
     */
    public function process_recertify_la($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recertify_la', $data);
    }

    /**
     * Process local_recertify_lg table.
     * @param stdClass $data
     */
    public function process_recertify_lg($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recertify_lg', $data);
    }

    /**
     * Process local_recertify_lt table.
     * @param stdClass $data
     */
    public function process_recertify_lt($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recertify_lt', $data);
    }

    /**
     * Process local_recertify_lb table.
     * @param stdClass $data
     */
    public function process_recertify_lb($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recertify_lb', $data);
    }

    /**
     * Process local_recertify_lo table.
     * @param stdClass $data
     */
    public function process_recertify_lo($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recertify_lo', $data);
    }

    /**
     * Process local_recertify_h5p table.
     * @param stdClass $data
     */
    public function process_recertify_h5p($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('local_recertify_h5p', $data);
        $this->set_mapping('recertify_h5p', $oldid, $newitemid);
    }

    /**
     * Process local_recertify_h5pr table.
     * @param stdClass $data
     */
    public function process_recertify_h5pr($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        // The results are nested below their attempt, so the archived attempt id comes from the parent.
        $data->attemptid = $this->get_new_parentid('recertify_h5p');

        $DB->insert_record('local_recertify_h5pr', $data);
    }

    /**
     * We call the after restore_course to update the coursemodule ids we didn't know when creating.
     */
    protected function after_restore_course() {
        global $DB;
        // Fix local_recertify_cmc records.
        $rcm = $DB->get_recordset('local_recertify_cmc', ['course' => $this->task->get_courseid()]);
        foreach ($rcm as $rc) {
            $rc->coursemoduleid = $this->get_mappingid('course_module', $rc->coursemoduleid);
            $DB->update_record('local_recertify_cmc', $rc);
        }
        $rcm->close();

        // Fix SCORM tracks.
        $rcm = $DB->get_recordset('local_recertify_sst', ['course' => $this->task->get_courseid()]);
        foreach ($rcm as $rc) {
            $rc->scormid = $this->get_mappingid('scorm', $rc->scormid);
            $rc->scoid = $this->get_mappingid('scorm_sco', $rc->scoid);
            $DB->update_record('local_recertify_sst', $rc);
        }
        $rcm->close();

        // Fix Quiz.
        $rcm = $DB->get_recordset('local_recertify_qg', ['course' => $this->task->get_courseid()]);
        foreach ($rcm as $rc) {
            $rc->quiz = $this->get_mappingid('quiz', $rc->quiz);
            $DB->update_record('local_recertify_qg', $rc);
        }
        $rcm->close();

        $rcm = $DB->get_recordset('local_recertify_qa', ['course' => $this->task->get_courseid()]);
        foreach ($rcm as $rc) {
            $rc->quiz = $this->get_mappingid('quiz', $rc->quiz);
            $rc->uniqueid = $this->get_mappingid('question_usage', $rc->uniqueid);
            $DB->update_record('local_recertify_qa', $rc);
        }
        $rcm->close();

        // Fix Choice answers.
        $rcm = $DB->get_recordset('local_recertify_cha', ['course' => $this->task->get_courseid()]);
        foreach ($rcm as $rc) {
            $rc->choiceid = $this->get_mappingid('choice', $rc->choiceid);
            $DB->update_record('local_recertify_cha', $rc);
        }
        $rcm->close();

        // Fix course module views.
        $rcm = $DB->get_recordset('local_recertify_cmv', ['course' => $this->task->get_courseid()]);
        foreach ($rcm as $rc) {
            $rc->coursemoduleid = $this->get_mappingid('course_module', $rc->coursemoduleid);
            $DB->update_record('local_recertify_cmv', $rc);
        }
        $rcm->close();

        // Fix H5P attempts.
        $rcm = $DB->get_recordset('local_recertify_h5p', ['course' => $this->task->get_courseid()]);
        foreach ($rcm as $rc) {
            $rc->h5pactivityid = $this->get_mappingid('h5pactivity', $rc->h5pactivityid);
            $DB->update_record('local_recertify_h5p', $rc);
        }
        $rcm->close();

        // Fix lesson archives.

        $rcm = $DB->get_recordset('local_recertify_la', ['course' => $this->task->get_courseid()]);
        foreach ($rcm as $rc) {
            $rc->lessonid = $this->get_mappingid('lesson', $rc->lessonid);
            $DB->update_record('local_recertify_la', $rc);
        }
        $rcm->close();

        $rcm = $DB->get_recordset('local_recertify_lg', ['course' => $this->task->get_courseid()]);
        foreach ($rcm as $rc) {
            $rc->lessonid = $this->get_mappingid('lesson', $rc->lessonid);
            $DB->update_record('local_recertify_lg', $rc);
        }
        $rcm->close();

        $rcm = $DB->get_recordset('local_recertify_lt', ['course' => $this->task->get_courseid()]);
        foreach ($rcm as $rc) {
            $rc->lessonid = $this->get_mappingid('lesson', $rc->lessonid);
            $DB->update_record('local_recertify_lt', $rc);
        }
        $rcm->close();

        $rcm = $DB->get_recordset('local_recertify_lb', ['course' => $this->task->get_courseid()]);
        foreach ($rcm as $rc) {
            $rc->lessonid = $this->get_mappingid('lesson', $rc->lessonid);
            $DB->update_record('local_recertify_lb', $rc);
        }
        $rcm->close();

        $rcm = $DB->get_recordset('local_recertify_lo', ['course' => $this->task->get_courseid()]);
        foreach ($rcm as $rc) {
            $rc->lessonid = $this->get_mappingid('lesson', $rc->lessonid);
            $DB->update_record('local_recertify_lo', $rc);
        }
        $rcm->close();
    }
}
