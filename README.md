# Course Recertify (local_recertify)

A Moodle plugin for automatic course re-certification by resetting completion
data after a configurable time period and notifying learners and supervisors.

## Supported Moodle Versions

| Moodle Version | Branch |
| -------------- | ------ |
| Moodle 4.5+    | main   |

## Description

This plugin adds course-level settings for recertification -- clearing course
and activity completion data for a user after a defined duration, notifying the
student to return and re-complete the course. It is designed for annual
re-certification workflows.

The following data is cleared during recertification:
- All activity grades (saved to standard grade history tables).
- All activity and course completion flags (with optional archiving).

### Supported Activity Modules

- **Quiz** -- Delete or keep existing attempts; optional archiving.
- **SCORM** -- Delete existing attempts; optional archiving.
- **Assignment** -- Grant additional attempts if configured.
- **Custom Certificate** -- Archive issued certificates.
- **LTI** -- Archive LTI access data.
- **Choice** -- Archive choice answers.
- **Questionnaire** -- Archive questionnaire responses.
- **Pulse** -- Clear activity completion.

### Supervisor Notifications

Supervisors assigned via a configurable role in user contexts receive
periodic email reports about learner progress and overdue recertifications.

## Installation

### Via Git

    cd /path/to/moodle
    git clone https://github.com/vds-gmbh/moodle-local_recertify.git local/recertify

### Manually

1. Download the ZIP and extract it.
2. Copy the `recertify` folder to `<moodle-root>/local/recertify`.
3. Visit Site Administration > Notifications to complete installation.

## Migration from local_recompletion

If `local_recompletion` or `local_recompletionextension` is present, the
install hook automatically migrates archive tables, course configuration,
plugin settings, and the reset log. After migration, the predecessor plugins
can be safely uninstalled via Site Administration > Plugins > Plugins overview.

## Configuration

After installation, visit Site Administration > Plugins > Local plugins >
Course recertify to configure global settings (supervisor role, email
templates). Per-course settings are available in the course administration
menu.

## Requirements

- Moodle 4.5 or higher
- PHP 8.1 or higher

## License

Licensed under the [GNU GPL v3 or later](https://www.gnu.org/copyleft/gpl.html).

## Credits

- Based on [local_recompletion](https://moodle.org/plugins/local_recompletion)
  by Dan Marsden and contributors at Catalyst IT.
- Supervisor notification and email features by Philipp Steingrebe.
- Maintained by [VdS Schadenverhütung GmbH](https://github.com/vds-gmbh).
