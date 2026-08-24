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
 * Defines backup_local_recertify class.
 *
 * @package     local_recertify
 * @copyright  2018 Catalyst IT
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Backup plugin class.
 *
 * @package    local_recertify
 * @copyright  2018 Catalyst IT
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_local_recertify_plugin extends backup_local_plugin {
    /**
     * Returns the format information to attach to course element.
     */
    protected function define_course_plugin_structure() {

        // Are we including usercompletion info in this backup.
        $usercompletion = $this->get_setting_value('userscompletion');

        $plugin = $this->get_plugin_element();
        $recertify = new backup_nested_element($this->get_recommended_name());

        $recertifydata = new backup_nested_element('recertify_config', null, [
            'course', 'name', 'value']);

        // Handle Historical course completions.
        // NOTE: all element names below are prefixed with "recertify_" so they do not clash
        // with the identically-structured elements defined by local_recompletion (from which
        // this plugin was forked). Course backup attaches every local plugin to the same
        // "multiple" optigroup, which requires element names to be unique across all plugins;
        // sharing names triggers error/multiple_optigroup_duplicate_element.
        $cc = new backup_nested_element('recertify_course_completion');

        $coursecompletions = new backup_nested_element('recertify_coursecompletion', ['id'], [
            'userid', 'course', 'timeenrolled', 'timestarted', 'timecompleted', 'reaggregate',
        ]);

        // Now Handle historical course_completion_crit_compl table.
        $criteriacompletions = new backup_nested_element('recertify_cc_crit_completions');

        $criteriacomplete = new backup_nested_element('recertify_cc_crit_compl', ['id'], [
            'criteriaid', 'userid', 'gradefinal', 'unenrolled', 'timecompleted',
        ]);

        $completions = new backup_nested_element('recertify_completions');

        $completion = new backup_nested_element('recertify_completion', ['id'], [
            'userid', 'completionstate', 'viewed', 'timemodified', 'coursemoduleid', 'course']);

        $plugin->add_child($recertify);
        $recertify->add_child($recertifydata);
        $recertify->add_child($cc);
        $cc->add_child($coursecompletions);
        $cc->add_child($criteriacompletions);
        $criteriacompletions->add_child($criteriacomplete);
        $cc->add_child($completions);
        $completions->add_child($completion);

        // Set source to populate the data.
        $recertifydata->set_source_table('local_recertify_config', [
            'course' => backup::VAR_PARENTID]);

        // Only include the archive info if usercompletion is also being saved to backup.
        if ($usercompletion) {
            $coursecompletions->set_source_table('local_recertify_cc', ['course' => backup::VAR_COURSEID]);
            $criteriacomplete->set_source_table('local_recertify_cc_cc', ['course' => backup::VAR_COURSEID]);
            $completion->set_source_table('local_recertify_cmc', ['course' => backup::VAR_COURSEID]);
        }
        $coursecompletions->annotate_ids('user', 'userid');
        $criteriacomplete->annotate_ids('user', 'userid');
        $criteriacomplete->annotate_ids('course_completion_criteria', 'criteriaid');
        $completion->annotate_ids('user', 'userid');
        $completion->annotate_ids('course_module', 'coursemoduleid');

        // Now deal with Quiz Archive tables.
        $quizgrades = new backup_nested_element('recertify_quizgrades');

        $grade = new backup_nested_element('recertify_grade', ['id'], [
            'userid', 'quiz', 'gradeval', 'timemodified', 'course']);

        $quizattempts = new backup_nested_element('recertify_quizattempts');

        $attempt = new backup_nested_element('recertify_attempt', ['id'], [
            'userid', 'attempt', 'uniqueid', 'layout', 'currentpage', 'preview', 'quiz',
            'state', 'timestart', 'timefinish', 'timemodified', 'timemodifiedoffline',
            'gradednotificationsenttime', 'timecheckstate', 'sumgrades', 'course']);

        $recertify->add_child($quizgrades);
        $quizgrades->add_child($grade);
        $recertify->add_child($quizattempts);
        $quizattempts->add_child($attempt);
        if ($usercompletion) {
            $attempt->set_source_table('local_recertify_qa', ['course' => backup::VAR_COURSEID]);
            $grade->set_source_table('local_recertify_qg', ['course' => backup::VAR_COURSEID]);
        }

        $attempt->annotate_ids('user', 'userid');
        $grade->annotate_ids('user', 'userid');

        // Now deal with SCORM archive tables.
        $scotracks = new backup_nested_element('recertify_scormtracks');

        $scotrack = new backup_nested_element('recertify_sco_track', ['id'], [
            'userid', 'attempt', 'element', 'value',
            'timemodified', 'course', 'scormid', 'scoid']);

        $recertify->add_child($scotracks);
        $scotracks->add_child($scotrack);

        if ($usercompletion) {
            $scotrack->set_source_table('local_recertify_sst', ['course' => backup::VAR_COURSEID]);
        }
        $scotrack->annotate_ids('user', 'userid');

        // Now deal with choice archive tables.
        $choiceanswers = new backup_nested_element('recertify_choiceanswers');

        $choiceanswer = new backup_nested_element('recertify_choiceanswer', ['id'], [
            'choiceid', 'userid', 'optionid', 'timemodified', 'choice']);

        $recertify->add_child($choiceanswers);
        $choiceanswers->add_child($choiceanswer);

        if ($usercompletion) {
            $choiceanswer->set_source_table('local_recertify_cha', ['course' => backup::VAR_COURSEID]);
        }
        $choiceanswer->annotate_ids('user', 'userid');

        // Now deal with the course module viewed archive table.
        $moduleviews = new backup_nested_element('recertify_moduleviews');

        $moduleview = new backup_nested_element('recertify_moduleview', ['id'], [
            'coursemoduleid', 'userid', 'timecreated', 'course']);

        $recertify->add_child($moduleviews);
        $moduleviews->add_child($moduleview);

        if ($usercompletion) {
            $moduleview->set_source_table('local_recertify_cmv', ['course' => backup::VAR_COURSEID]);
        }
        $moduleview->annotate_ids('user', 'userid');
        $moduleview->annotate_ids('course_module', 'coursemoduleid');

        // Now deal with the H5P archive tables. The results are nested below their attempt so the
        // restore can resolve the archived attempt id through get_new_parentid().
        $h5ps = new backup_nested_element('recertify_h5ps');

        $h5p = new backup_nested_element('recertify_h5p', ['id'], [
            'originalattemptid', 'h5pactivityid', 'userid', 'timecreated', 'timemodified', 'attempt',
            'rawscore', 'maxscore', 'scaled', 'duration', 'completion', 'success', 'course']);

        $h5presults = new backup_nested_element('recertify_h5presults');

        $h5presult = new backup_nested_element('recertify_h5presult', ['id'], [
            'attemptid', 'subcontent', 'timecreated', 'interactiontype', 'description', 'correctpattern',
            'response', 'additionals', 'rawscore', 'maxscore', 'duration', 'completion', 'success', 'course']);

        $recertify->add_child($h5ps);
        $h5ps->add_child($h5p);
        $h5p->add_child($h5presults);
        $h5presults->add_child($h5presult);

        if ($usercompletion) {
            $h5p->set_source_table('local_recertify_h5p', ['course' => backup::VAR_COURSEID]);
            $h5presult->set_source_table(
                'local_recertify_h5pr',
                ['course' => backup::VAR_COURSEID, 'attemptid' => backup::VAR_PARENTID]
            );
        }
        $h5p->annotate_ids('user', 'userid');

        // Now deal with the lesson archive tables.
        $lessonattempts = new backup_nested_element('recertify_lessonattempts');

        $lessonattempt = new backup_nested_element('recertify_lessonattempt', ['id'], [
            'lessonid', 'pageid', 'userid', 'answerid', 'retry', 'correct', 'useranswer', 'timeseen', 'course']);

        $lessongrades = new backup_nested_element('recertify_lessongrades');

        $lessongrade = new backup_nested_element('recertify_lessongrade', ['id'], [
            'lessonid', 'userid', 'grade', 'late', 'completed', 'course']);

        $lessontimers = new backup_nested_element('recertify_lessontimers');

        $lessontimer = new backup_nested_element('recertify_lessontimer', ['id'], [
            'lessonid', 'userid', 'starttime', 'lessontime', 'completed', 'timemodifiedoffline', 'course']);

        $lessonbranches = new backup_nested_element('recertify_lessonbranches');

        $lessonbranch = new backup_nested_element('recertify_lessonbranch', ['id'], [
            'lessonid', 'userid', 'pageid', 'retry', 'flag', 'timeseen', 'nextpageid', 'course']);

        $lessonoverrides = new backup_nested_element('recertify_lessonoverrides');

        $lessonoverride = new backup_nested_element('recertify_lessonoverride', ['id'], [
            'lessonid', 'groupid', 'userid', 'available', 'deadline', 'timelimit', 'review',
            'maxattempts', 'retake', 'password', 'course']);

        $recertify->add_child($lessonattempts);
        $lessonattempts->add_child($lessonattempt);
        $recertify->add_child($lessongrades);
        $lessongrades->add_child($lessongrade);
        $recertify->add_child($lessontimers);
        $lessontimers->add_child($lessontimer);
        $recertify->add_child($lessonbranches);
        $lessonbranches->add_child($lessonbranch);
        $recertify->add_child($lessonoverrides);
        $lessonoverrides->add_child($lessonoverride);

        if ($usercompletion) {
            $lessonattempt->set_source_table('local_recertify_la', ['course' => backup::VAR_COURSEID]);
            $lessongrade->set_source_table('local_recertify_lg', ['course' => backup::VAR_COURSEID]);
            $lessontimer->set_source_table('local_recertify_lt', ['course' => backup::VAR_COURSEID]);
            $lessonbranch->set_source_table('local_recertify_lb', ['course' => backup::VAR_COURSEID]);
            $lessonoverride->set_source_table('local_recertify_lo', ['course' => backup::VAR_COURSEID]);
        }

        $lessonattempt->annotate_ids('user', 'userid');
        $lessongrade->annotate_ids('user', 'userid');
        $lessontimer->annotate_ids('user', 'userid');
        $lessonbranch->annotate_ids('user', 'userid');
        $lessonoverride->annotate_ids('user', 'userid');

        return $plugin;
    }
}
