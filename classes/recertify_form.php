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
 * Edit course completion settings - the form definition.
 *
 * @package     local_recertify
 * @copyright  2017 Dan Marsden
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Defines the course completion settings form.
 *
 * @copyright  2017 Dan Marsden
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_recertify_recertify_form extends moodleform {
    /**
     * Defines the form fields.
     */
    public function definition() {

        $mform = $this->_form;
        $course = $this->_customdata['course'];
        $config = get_config('local_recertify');

        $context = \context_course::instance($course->id);

        $editoroptions = [
            'subdirs' => 0,
            'maxbytes' => 0,
            'maxfiles' => 0,
            'changeformat' => 0,
            'context' => $context,
            'noclean' => 0,
            'trusttext' => 0,
            'cols' => '50',
            'rows' => '8',
        ];

        $mform->addElement('checkbox', 'enable', get_string('enablerecertify', 'local_recertify'));
        $mform->addHelpButton('enable', 'enablerecertify', 'local_recertify');

        $options = ['optional' => false, 'defaultunit' => 86400];
        $mform->addElement('duration', 'recertifyduration', get_string('recertifyrange', 'local_recertify'), $options);
        $mform->addHelpButton('recertifyduration', 'recertifyrange', 'local_recertify');
        $mform->disabledIf('recertifyduration', 'enable', 'notchecked');
        $mform->setDefault('recertifyduration', $config->duration);

        $mform->addElement('checkbox', 'recertifyemailenable', get_string('recertifyemailenable', 'local_recertify'));
        $mform->setDefault('recertifyemailenable', $config->emailenable);
        $mform->addHelpButton('recertifyemailenable', 'recertifyemailenable', 'local_recertify');
        $mform->disabledIf('recertifyemailenable', 'enable', 'notchecked');

        // Email Notification settings.
        $mform->addElement('header', 'emailheader', get_string('emailrecertifytitle', 'local_recertify'));
        $mform->setExpanded('emailheader', false);
        $mform->addElement(
            'text',
            'recertifyemailsubject',
            get_string('recertifyemailsubject', 'local_recertify'),
            'size = "80"'
        );
        $mform->setType('recertifyemailsubject', PARAM_TEXT);
        $mform->addHelpButton('recertifyemailsubject', 'recertifyemailsubject', 'local_recertify');
        $mform->disabledIf('recertifyemailsubject', 'enable', 'notchecked');
        $mform->disabledIf('recertifyemailsubject', 'recertifyemailenable', 'notchecked');
        $mform->setDefault('recertifyemailsubject', $config->emailsubject);

        $mform->addElement(
            'editor',
            'recertifyemailbody',
            get_string('recertifyemailbody', 'local_recertify'),
            $editoroptions
        );
        $mform->setDefault('recertifyemailbody', ['text' => $config->emailbody,
            'format' => FORMAT_HTML]);
        $mform->addHelpButton('recertifyemailbody', 'recertifyemailbody', 'local_recertify');
        $mform->disabledIf('recertifyemailbody', 'enable', 'notchecked');
        $mform->disabledIf('recertifyemailbody', 'recertifyemailenable', 'notchecked');

        // Email supervisor notification settings
        $mform->addElement('checkbox', 'supervisoremailenable', get_string('supervisoremailenable', 'local_recertify'));
        $mform->setDefault('supervisoremailenable', 1);
        $mform->addHelpButton('supervisoremailenable', 'supervisoremailenable', 'local_recertify');
        $mform->disabledIf('recertifyemailenable', 'enable', 'notchecked');

        // Advanced recertify settings.
        // Delete data section.
        $mform->addElement('header', 'advancedheader', get_string('advancedrecertifytitle', 'local_recertify'));
        $mform->setExpanded('advancedheader', false);

        $mform->addElement('checkbox', 'deletegradedata', get_string('deletegradedata', 'local_recertify'));
        $mform->setDefault('deletegradedata', $config->deletegradedata);
        $mform->addHelpButton('deletegradedata', 'deletegradedata', 'local_recertify');

        $mform->addElement('checkbox', 'archivecompletiondata', get_string('archivecompletiondata', 'local_recertify'));
        // If we are forcing completion data archive, always be ticked.
        $archivedefault = $config->forcearchivecompletiondata ? 1 : $config->archivecompletiondata;
        $mform->setDefault('archivecompletiondata', $archivedefault);
        $mform->addHelpButton('archivecompletiondata', 'archivecompletiondata', 'local_recertify');

        // Get all plugins that are supported.
        $plugins = local_recertify_get_supported_plugins();
        foreach ($plugins as $plugin) {
            $fqn = 'local_recertify\\plugins\\' . $plugin;
            $fqn::editingform($mform);
        }

        $mform->disabledIf('deletegradedata', 'enable', 'notchecked');
        $mform->disabledIf('archivecompletiondata', 'enable', 'notchecked');
        $mform->disabledIf('archivecompletiondata', 'forcearchive', 'eq');

        // Add common action buttons.
        $this->add_action_buttons();

        // Add hidden fields.
        $mform->addElement('hidden', 'course', $course->id);
        $mform->setType('course', PARAM_INT);
        $mform->addElement('hidden', 'forcearchive', $config->forcearchivecompletiondata);
        $mform->setType('forcearchive', PARAM_BOOL);
    }
}
