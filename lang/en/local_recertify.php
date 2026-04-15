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
 * Strings for local_recertify
 *
 * @package    local_recertify
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Course recertify';
$string['recertify'] = 'recertify';
$string['editrecertify'] = 'Edit course recertify settings';
$string['enablerecertify'] = 'Enable recertify';
$string['enablerecertify_help'] = 'The recertify plugin allows a course completion details to be reset after a defined period.';
$string['recertifyrange'] = 'Recertify period';
$string['recertifyrange_help'] = 'Set the period of time before a users completion results are reset.';
$string['recertifysettingssaved'] = 'Recertify settings saved';
$string['recertify:manage'] = 'Allow course recertify settings to be changed';
$string['recertify:resetmycompletion'] = 'Reset my own completion';
$string['resetmycompletion'] = 'Reset my activity completion';
$string['recertifytask'] = 'Check for users that need to recomplete';
$string['completionnotenabled'] = 'Completion is not enabled in this course';
$string['recertifynotenabled'] = 'Recertify is not enabled in this course';
$string['recertifyemailenable'] = 'Send recertify message';
$string['recertifyemailenable_help'] = 'Enable email messaging to notifiy users that recertify is required';
$string['recertifyemailsubject'] = 'Recertify message subject';
$string['recertifyemailsubject_help'] = 'A custom recertify email subject may be added as plain text

The following placeholders may be included in the message:

* Course name {$a->coursename}
* User fullname {$a->fullname}';
$string['recertifyemaildefaultsubject'] = 'Course {$a->coursename} recertify required';
$string['recertifyemailbody'] = 'Recertify message body';
$string['recertifyemailbody_help'] = 'A custom recertify email subject may be added as plain text or Moodle-auto format, including HTML tags and multi-lang tags

The following placeholders may be included in the message:

* Course name {$a->coursename}
* Link to course {$a->link}
* Link to user\'s profile page {$a->profileurl}
* User email {$a->email}
* User fullname {$a->fullname}';
$string['recertifyemaildefaultbody'] = 'Hi there, please recomplete the course {$a->coursename} {$a->link}';
$string['advancedrecertifytitle'] = 'Advanced';
$string['deletegradedata'] = 'Delete all grades for the user';
$string['deletegradedata_help'] = 'Delete current grade completion data from grade_grades table. Grade recertify data is permanently deleted but data retained in Grade history data table.';
$string['archivecompletiondata'] = 'Archive completion data';
$string['archivecompletiondata_help'] = 'Writes completion data to the local_recertify_cc, local_recertify_cc_cc and local_recertify_cmc tables. Completion data will be permanently deleted if this is not selected.';
$string['forcearchivecompletiondata'] = 'Force archive completion data';
$string['forcearchivecompletiondata_help'] = 'If enabled, completion data archiving will be forced on for all course recertifys. This can prevent accidental data loss.';
$string['emailrecertifytitle'] = 'Custom recertify message settings';
$string['eventrecertify'] = 'Course recertify';
$string['assignattempts'] = 'Assign attempts';
$string['assignattempts_help'] = 'How to handle assignment attempts within the course.
If the setting \'Update on grade change\' is used, when a teacher updates the grade inside an assignment activity and the user has already completed the course, their course completion date will be updated to use the date of the assignment grade change.';
$string['extraattempt'] = 'Give student extra attempt/s';
$string['quizattempts'] = 'Quiz attempts';
$string['quizattempts_help'] = 'What to do with existing Quiz attempts. If delete and archive is selected, the old quiz attempts will be archived in the local_recertify tables,
 if set to give extra attempts this will add a quiz override to allow the user to have the maximum number of allowed attempts set.';
$string['questionnaireattempts'] = 'Questionnaire attempts';
$string['questionnaireattempts_help'] = 'What to do with existing Questionnaire attempts. If delete and archive is selected, the old Questionnaire attempts will be archived in the local_recertify tables.';
$string['scormattempts'] = 'SCORM attempts';
$string['scormattempts_help'] = 'Should existing SCORM attempts be deleted - if archive is selected, the old SCORM attempts will be archived in the local_recertify_sst table.';
$string['archive'] = 'Archive old attempts';
$string['delete'] = 'Delete existing attempts';
$string['donothing'] = 'Do nothing';
$string['resetcompletionconfirm'] = 'Are you sure you want to reset all completion data in this course for {$a}?  Warning - this may permanently delete some submitted content.';
$string['privacy:metadata:local_recertify_cc'] = 'Archive of previous course completions.';
$string['privacy:metadata:local_recertify_cmc'] = 'Archive of previous course module completions.';
$string['privacy:metadata:local_recertify_cc_cc'] = 'Archive of previous course_completion_crit_compl';
$string['privacy:metadata:local_recertify_cha'] = 'Archive of choice answers';
$string['privacy:metadata:local_recertify_cha:choiceid'] = 'The Choice ID of the Archive of choice answers';
$string['privacy:metadata:local_recertify_cha:optionid'] = 'The Option ID of the Archive of choice answers';
$string['privacy:metadata:local_recertify_ltia'] = 'User access log and gradeback data.';
$string['privacy:metadata:local_recertify_ltia:toolid'] = 'The ID of the tool of LTI enrolment method.';
$string['privacy:metadata:local_recertify_ltia:userid'] = 'The ID of the user.';
$string['privacy:metadata:local_recertify_ltia:lastgrade'] = 'The last grade the user was recorded of having.';
$string['privacy:metadata:local_recertify_ltia:lastaccess'] = 'The time when the user last accessed the course.';
$string['privacy:metadata:local_recertify_ltia:timecreated'] = 'The time when the user was enrolled.';
$string['privacy:metadata:userid'] = 'The user ID linked to this table.';
$string['privacy:metadata:course'] = 'The course ID linked to this table.';
$string['privacy:metadata:timecompleted'] = 'The time that the course was completed.';
$string['privacy:metadata:timeenrolled'] = 'The time that the user was enrolled in the course';
$string['privacy:metadata:timemodified'] = 'The time that the record was modified';
$string['privacy:metadata:timestarted'] = 'The time the course was started.';
$string['privacy:metadata:coursesummary'] = 'Stores the course completion data for a user.';
$string['privacy:metadata:gradefinal'] = 'Final grade received for course completion';
$string['privacy:metadata:overrideby'] = 'The user ID of the person who overrode the activity completion';
$string['privacy:metadata:reaggregate'] = 'If the course completion was reaggregated.';
$string['privacy:metadata:unenroled'] = 'If the user has been unenrolled from the course';
$string['privacy:metadata:quiz_attempts'] = 'Archived details about each attempt on a quiz.';
$string['privacy:metadata:quiz_attempts:attempt'] = 'The attempt number.';
$string['privacy:metadata:quiz_attempts:currentpage'] = 'The current page that the user is on.';
$string['privacy:metadata:quiz_attempts:preview'] = 'Whether this is a preview of the quiz.';
$string['privacy:metadata:quiz_attempts:state'] = 'The current state of the attempt.';
$string['privacy:metadata:quiz_attempts:sumgrades'] = 'The sum of grades in the attempt.';
$string['privacy:metadata:quiz_attempts:timecheckstate'] = 'The time that the state was checked.';
$string['privacy:metadata:quiz_attempts:timefinish'] = 'The time that the attempt was completed.';
$string['privacy:metadata:quiz_attempts:timemodified'] = 'The time that the attempt was updated.';
$string['privacy:metadata:quiz_attempts:timemodifiedoffline'] = 'The time that the attempt was updated via an offline update.';
$string['privacy:metadata:quiz_attempts:timestart'] = 'The time that the attempt was started.';
$string['privacy:metadata:quiz_grades'] = 'Archived details about the overall grade for previous quiz attempts.';
$string['privacy:metadata:quiz_grades:grade'] = 'The overall grade for this quiz.';
$string['privacy:metadata:quiz_grades:quiz'] = 'The quiz that was graded.';
$string['privacy:metadata:quiz_grades:timemodified'] = 'The time that the grade was modified.';
$string['privacy:metadata:quiz_grades:userid'] = 'The user who was graded.';
$string['privacy:metadata:scoes_track:element'] = 'The name of the element to be tracked';
$string['privacy:metadata:scoes_track:value'] = 'The value of the given element';
$string['privacy:metadata:coursemoduleid'] = 'The activity ID';
$string['privacy:metadata:completionstate'] = 'If the activity has been completed';
$string['privacy:metadata:viewed'] = 'If the activity was viewed';
$string['privacy:metadata:attempt'] = 'The attempt number';
$string['privacy:metadata:scorm_scoes_track'] = 'Archive of the tracked data of the SCOes belonging to the activity';
$string['privacy:metadata:local_recertify_qr:questionnaireid'] = 'Questionnaire id';
$string['privacy:metadata:local_recertify_qr:submitted'] = 'Submitted';
$string['privacy:metadata:local_recertify_qr:complete'] = 'complete';
$string['privacy:metadata:local_recertify_qr:grade'] = 'Grade';
$string['privacy:metadata:local_recertify_qr'] = 'Recertify Questionnaire response table';
$string['noassigngradepermission'] = 'Your completion was reset, but this course contains an assignment that could not be reset, please ask your teacher to do this for you if required.';
$string['editcompletion'] = 'Edit course completion date';
$string['editcompletion_desc'] = 'Modify the course completion date for the following users:';
$string['coursecompletiondate'] = 'New course completion date';
$string['completionupdated'] = 'Course completion dates were updated';
$string['bulkchangedate'] = 'Change completion date for selected users';
$string['nousersselected'] = 'No users were selected';
$string['resetallcompletion'] = 'Reset all completion';
$string['bulkresetallcompletion'] = 'Reset all completion for selected users';
$string['resetcompletionfor'] = 'Reset completion for {$a}';
$string['completionresetuser'] = 'Completion for {$a} in this course has been reset.';
$string['completionreset'] = 'Completion for the selected students in this course has been reset.';
$string['modifycompletiondates'] = 'Modify course completion dates';
$string['assignevent'] = 'Update course completion on grade change';
$string['defaultsettings'] = 'Recertify default settings';
$string['archivequiz'] = 'Archive old quiz attempts';
$string['archivequestionnaire'] = 'Archive old questionnaire attempts';
$string['archivescorm'] = 'Archive old SCORM attempts';
$string['resetlti'] = 'Reset LTI grades';
$string['resetltis'] = 'LTI grades';
$string['resetltis_help'] = 'How to handle LTI grades within the course.
If the setting \'Reset LTI grades\' is used, all grade LTI results will be reset to 0.
When user achieved new completion in the course, the updated course grade will be resend to the LTI provider.';
$string['pulsenotifications'] = 'Pulse notifications';
$string['pulsenotifications_help'] = 'Should Pulse notifications which have already been sent be reset?';
$string['pulseresetnotifications'] = 'Reset notifications';
$string['choiceattempts'] = "Choice attempts";
$string['archivechoice'] = "Archive old choice attempts";
$string['choiceattempts_help'] = 'Should existing Choice attempts be deleted - if archive is selected, the old Choice attempts will be archived in the local_recertify_cha table.';
$string['customcertcertificates'] = 'Custom certificates';
$string['customcertcertificates_help'] = 'Should issued custom certificates be deleted?';
$string['customcertresetcertificates'] = 'Delete issued certificates';
$string['customcertresetcertificatesverifywarn'] = 'Attention: Deleting the issued certificates, even if you archive them before the deletion, will result in the fact that issues certificates cannot be verified anymore in Moodle. Please only delete the certificates if this is acceptable for you.';
$string['archivecustomcertcertificates'] = 'Archive issued certificates';
$string['archivecustomcertcertificates_help'] = 'Should issued custom certificates be archived?';



$string['adminemail:subject'] = '[Kopie] ';
$string['supervisor_notificationtask'] = 'Check for incompleted users and notify their supervisor';
$string['supervisoremail:courselink'] = 'mit dieser E-Mail erhalten Sie den aktuellen Statusbericht für den VdS-Kurs';
$string['supervisoremail:email'] = 'E-Mail';
$string['supervisoremail:firstname'] = 'Vorname';
$string['supervisoremail:greeting'] = 'Hallo ';
$string['supervisoremail:heading'] = 'Statusbericht';
$string['supervisoremail:lastname'] = 'Nachname';
$string['supervisoremail:status'] = 'Status';
$string['supervisoremail:status:completed'] = 'Gültig bis {$a}';
$string['supervisoremail:status:notstarted'] = 'Überfällig seit {$a} Tagen';
$string['supervisoremail:status:started'] = 'Überfällig seit {$a} Tagen (in Bearbeitung)';
$string['supervisoremail:status:suspended'] = 'Inaktiv';
$string['supervisoremail:subject'] = 'Statusbericht für den VdS-Kurs {$a}';
$string['supervisoremailenable'] = 'Send supervisor status message';
$string['supervisoremailenable_help'] = 'Enable email messaging to notifiy supervisors about their employee status';
$string['supervisorrole'] = 'Supervisor role';
$string['supervisorrole_help'] = 'Role to use for sending supervisor emails.';
$string['useremail:overdue:greeting'] = 'Hallo';
$string['useremail:overdue:heading'] = 'Erinnerung';
$string['useremail:overdue:item'] = 'Dieser Teilnehmer wurde einmalig über die Fälligkeit dieses Kurses per E-Mail informiert.';
$string['useremail:overdue:message'] = 'mit dieser E-Mail möchten wir Sie an die Durchführung des VdS-Kurses <a href="{$a->courselink}">{$a->coursename}</a> erinnern.';
$string['useremail:password'] = 'Passwort';
$string['useremail:passwordreset:heading'] = 'Überfällige Mitarbeiter ohne E-Mail Adresse';
$string['useremail:passwordreset:item'] = 'Für diesen Teilnehmer ist keine E-Mail Adresse bekannt. Da die &quot;Passwort vergessen&quot;-Funktion in diesem Fall nicht genutzt werden kann, wurde ein neues Passwort vergeben.';
$string['useremail:passwordreset:message'] = 'Bitte informieren Sie folgende Mitarbeiter, dass sie den VdS-Kurs <a href="{$a->courselink}">{$a->coursename}</a> erneut abschließen müssen. Bitte teilen Sie diesen Mitarbeitern jeweils den Anmeldenamen und das Passwort mit. Mit diesen Daten können sich die Mitarbeiter unter <a href="https://elearning.vds.de">https://elearning.vds.de</a> anmelden.';
$string['useremail:subject'] = 'Erinnerung an VdS-Kurs {$a}';
$string['useremail:username'] = 'Anmeldename';
$string['useremail:template'] = <<<EMAIL_TEMPLATE
<!doctype html>
<html>
  <head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Simple Transactional Email</title>
    <style>
      /* -------------------------------------
          GLOBAL RESETS
      ------------------------------------- */
      
      /*All the styling goes here*/
      
      img {
        border: none;
        -ms-interpolation-mode: bicubic;
        max-width: 100%; 
      }

      body {
        background-color: #f6f6f6;
        font-family: sans-serif;
        -webkit-font-smoothing: antialiased;
        font-size: 14px;
        line-height: 1.4;
        margin: 0;
        padding: 0;
        -ms-text-size-adjust: 100%;
        -webkit-text-size-adjust: 100%; 
      }

      table {
        border-collapse: separate;
        mso-table-lspace: 0pt;
        mso-table-rspace: 0pt;
        width: 100%; }
        table td {
          font-family: sans-serif;
          font-size: 14px;
          vertical-align: top; 
      }

      /* -------------------------------------
          BODY & CONTAINER
      ------------------------------------- */

      .body {
        background-color: #f6f6f6;
        width: 100%; 
      }

      /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
      .container {
        display: block;
        margin: 0 auto !important;
        /* makes it centered */
        max-width: 580px;
        padding: 10px;
        width: 580px; 
      }

      /* This should also be a block element, so that it will fill 100% of the .container */
      .content {
        box-sizing: border-box;
        display: block;
        margin: 0 auto;
        max-width: 580px;
        padding: 10px; 
      }

      /* -------------------------------------
          HEADER, FOOTER, MAIN
      ------------------------------------- */
      .main {
        background: #ffffff;
        border-radius: 3px;
        width: 100%; 
      }

      .wrapper {
        box-sizing: border-box;
        padding: 20px; 
      }

      .content-block {
        padding-bottom: 10px;
        padding-top: 10px;
      }

      .footer {
        clear: both;
        margin-top: 10px;
        text-align: center;
        width: 100%; 
      }
        .footer td,
        .footer p,
        .footer span,
        .footer a {
          color: #999999;
          font-size: 12px;
          text-align: center; 
      }

      /* -------------------------------------
          TYPOGRAPHY
      ------------------------------------- */
      h1,
      h2,
      h3,
      h4 {
        color: #000000;
        font-family: sans-serif;
        font-weight: 400;
        line-height: 1.4;
        margin: 0;
        margin-bottom: 30px; 
      }

      h1 {
        font-size: 35px;
        font-weight: 300;
        text-align: center;
        text-transform: capitalize; 
      }

      p,
      ul,
      ol {
        font-family: sans-serif;
        font-size: 14px;
        font-weight: normal;
        margin: 0;
        margin-bottom: 15px; 
      }
        p li,
        ul li,
        ol li {
          list-style-position: inside;
          margin-left: 5px; 
      }

      a {
        color: #3498db;
        text-decoration: underline; 
      }

      .text-success {
        color: #28a745 !important;
      }

      .text-warning {
        color: #ffc107 !important;
      }

      .text-danger {
        color: #dc3545 !important;
      }

      .text-muted {
        color: #6c757d !important;
      }

      /* -------------------------------------
          BUTTONS
      ------------------------------------- */
      .btn {
        box-sizing: border-box;
        width: 100%; }
        .btn > tbody > tr > td {
          padding-bottom: 15px; }
        .btn table {
          width: auto; 
      }
        .btn table td {
          background-color: #ffffff;
          border-radius: 5px;
          text-align: center; 
      }
        .btn a {
          background-color: #ffffff;
          border: solid 1px #3498db;
          border-radius: 5px;
          box-sizing: border-box;
          color: #3498db;
          cursor: pointer;
          display: inline-block;
          font-size: 14px;
          font-weight: bold;
          margin: 0;
          padding: 12px 25px;
          text-decoration: none;
          text-transform: capitalize; 
      }

      .btn-primary table td {
        background-color: #3498db; 
      }

      .btn-primary a {
        background-color: #3498db;
        border-color: #3498db;
        color: #ffffff; 
      }

      /* -------------------------------------
          OTHER STYLES THAT MIGHT BE USEFUL
      ------------------------------------- */
      .last {
        margin-bottom: 0; 
      }

      .first {
        margin-top: 0; 
      }

      .align-center {
        text-align: center; 
      }

      .align-right {
        text-align: right; 
      }

      .align-left {
        text-align: left; 
      }

      .clear {
        clear: both; 
      }

      .mt0 {
        margin-top: 0; 
      }

      .mb0 {
        margin-bottom: 0; 
      }

      .preheader {
        color: transparent;
        display: none;
        height: 0;
        max-height: 0;
        max-width: 0;
        opacity: 0;
        overflow: hidden;
        mso-hide: all;
        visibility: hidden;
        width: 0; 
      }

      .powered-by a {
        text-decoration: none; 
      }

      hr {
        border: 0;
        border-bottom: 1px solid #f6f6f6;
        margin: 20px 0; 
      }

      /* -------------------------------------
          RESPONSIVE AND MOBILE FRIENDLY STYLES
      ------------------------------------- */
      @media only screen and (max-width: 620px) {
        table[class=body] h1 {
          font-size: 28px !important;
          margin-bottom: 10px !important; 
        }
        table[class=body] p,
        table[class=body] ul,
        table[class=body] ol,
        table[class=body] td,
        table[class=body] span,
        table[class=body] a {
          font-size: 16px !important; 
        }
        table[class=body] .wrapper,
        table[class=body] .article {
          padding: 10px !important; 
        }
        table[class=body] .content {
          padding: 0 !important; 
        }
        table[class=body] .container {
          padding: 0 !important;
          width: 100% !important; 
        }
        table[class=body] .main {
          border-left-width: 0 !important;
          border-radius: 0 !important;
          border-right-width: 0 !important; 
        }
        table[class=body] .btn table {
          width: 100% !important; 
        }
        table[class=body] .btn a {
          width: 100% !important; 
        }
        table[class=body] .img-responsive {
          height: auto !important;
          max-width: 100% !important;
          width: auto !important; 
        }
      }

      /* -------------------------------------
          PRESERVE THESE STYLES IN THE HEAD
      ------------------------------------- */
      @media all {
        .ExternalClass {
          width: 100%; 
        }
        .ExternalClass,
        .ExternalClass p,
        .ExternalClass span,
        .ExternalClass font,
        .ExternalClass td,
        .ExternalClass div {
          line-height: 100%; 
        }
        .apple-link a {
          color: inherit !important;
          font-family: inherit !important;
          font-size: inherit !important;
          font-weight: inherit !important;
          line-height: inherit !important;
          text-decoration: none !important; 
        }
        #MessageViewBody a {
          color: inherit;
          text-decoration: none;
          font-size: inherit;
          font-family: inherit;
          font-weight: inherit;
          line-height: inherit;
        }
        .btn-primary table td:hover {
          background-color: #34495e !important; 
        }
        .btn-primary a:hover {
          background-color: #34495e !important;
          border-color: #34495e !important; 
        } 
      }

    </style>
  </head>
  <body class="">
    <span class="preheader">{preheader}</span>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
      <tr>
        <td>&nbsp;</td>
        <td class="container">
          <div class="content">

            <!-- START CENTERED WHITE CONTAINER -->
            <table role="presentation" class="main">

              <!-- START MAIN CONTENT AREA -->
              <tr>
                <td class="wrapper">
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td>
                        {content}
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>

            <!-- END MAIN CONTENT AREA -->
            </table>
            <!-- END CENTERED WHITE CONTAINER -->

            <!-- START FOOTER -->
            <div class="footer">
              <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td class="content-block">
                    <span class="apple-link">VdS Schadenverhütung GmbH, Amsterdamer Str. 174, 50735 Köln</span>
                    <br>
                    <a href="https://vds.de">https://vds.de</a> | <a href="callto:+492217766505">Hotline +49 (0) 221 77 66 505</a> | <a href="mailto:elearning@vds.de">E-Mail elearning@vds.de</a>.
                  </td>
                </tr>
                <tr>
                  <td class="content-block">
                    VdS-Bildungszentrum 
                    <a href="https://bildung.vds.de/?tab=lehrgaenge">Lehrgänge</a>,
                    <a href="https://bildung.vds.de/?tab=fachtagungen">Fachtagungen</a>,
                    <a href="https://bildung.vds.de/?tab=elearning">E-Learning</a>.
                  </td>
                </tr>
              </table>
            </div>
            <!-- END FOOTER -->

          </div>
        </td>
        <td>&nbsp;</td>
      </tr>
    </table>
  </body>
</html>
EMAIL_TEMPLATE;
