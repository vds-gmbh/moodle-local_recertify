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
 * local recertify default settings
 *
 * @package    local_recertify
 * @copyright  2020 Catalyst IT
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    global $CFG;
    require_once($CFG->dirroot . '/local/recertify/locallib.php');
    $settings = new admin_settingpage('local_recertify', new lang_string('defaultsettings', 'local_recertify'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configduration(
        'local_recertify/duration',
        new lang_string('recertifyrange', 'local_recertify'),
        new lang_string('recertifyrange_help', 'local_recertify'),
        YEARSECS,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_recertify/emailenable',
        new lang_string('recertifyemailenable', 'local_recertify'),
        new lang_string('recertifyemailenable_help', 'local_recertify'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'local_recertify/emailsubject',
        new lang_string('recertifyemailsubject', 'local_recertify'),
        new lang_string('recertifyemailsubject_help', 'local_recertify'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_confightmleditor(
        'local_recertify/emailbody',
        new lang_string('recertifyemailbody', 'local_recertify'),
        new lang_string('recertifyemailbody_help', 'local_recertify'),
        ''
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_recertify/deletegradedata',
        new lang_string('deletegradedata', 'local_recertify'),
        new lang_string('deletegradedata_help', 'local_recertify'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_recertify/archivecompletiondata',
        new lang_string('archivecompletiondata', 'local_recertify'),
        new lang_string('archivecompletiondata_help', 'local_recertify'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_recertify/forcearchivecompletiondata',
        new lang_string('forcearchivecompletiondata', 'local_recertify'),
        new lang_string('forcearchivecompletiondata_help', 'local_recertify'),
        0
    ));

    $roles = get_roles_for_contextlevels(CONTEXT_USER);
    $names = role_get_names();
    $options = [];
    foreach ($roles as $idx => $roleid) {
        $options[$roleid] = $names[$roleid]->localname;
    }
    // Sometimes there are no options.
    if ($options) {
        $settings->add(new admin_setting_configselect(
            'local_recertify/supervisorrole',
            get_string('supervisorrole', 'local_recertify'),
            get_string('supervisorrole_help', 'local_recertify'),
            null,
            $options
        ));
    } else {
        $settings->add(new admin_setting_configempty(
            'local_recertify/supervisorrole',
            get_string('supervisorrole', 'local_recertify'),
            get_string('supervisorrole_help', 'local_recertify')
        ));
    }


    $plugins = local_recertify_get_supported_plugins();
    foreach ($plugins as $plugin) {
        $fqn = 'local_recertify\\plugins\\' . $plugin;
        $fqn::settings($settings);
    }
}
