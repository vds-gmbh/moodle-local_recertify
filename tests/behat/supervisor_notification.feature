@synergylearning @local @local_recertify @javascript
Feature: Supervisor notifications are sent
  As a supervisor
  I receive notifications about learner recertifys

  Background:
    Given the following "categories" exist:
      | name      | category | idnumber |
      | Category1 | 0        | CAT1     |
      | Category2 | 0        | CAT2     |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1        | CAT1     | 1                |
    And the following "activities" exist:
      | activity | course | name   | idnumber |
      | page     | C1     | Page 1 | page1    |
      | page     | C1     | Page 2 | page2    |
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | teacher1 | C1     | editingteacher |
    And I log in as "admin"
    And I add context type "User" for the "Teacher" role
    And the following "role assigns" exist:
      | user     | role           | contextlevel | reference |
      | teacher1 | editingteacher | User         | student1  |
    And the following config values are set as admin:
      | supervisorrole | 3 | local_recertify |
    And the following "local_recertify > course completions" exist:
      | user     | course | complete | timestamp |
      | student1 | C1     | complete | ##today## |
    And I am on "Course 1" course homepage
    And I navigate to "Course recertify" in current page administration
    And I set the following fields to these values:
      | Enable recertify            | 1       |
      | recertifyduration[number]   | 10      |
      | recertifyduration[timeunit] | seconds |
      | Send recertify message      | 1       |
    And I press "Save changes"

  Scenario: Admin user receives email
    Given I run the scheduled task "\local_recertify\task\supervisor_notification"
