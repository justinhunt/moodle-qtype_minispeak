@qtype @qtype_minispeak
Feature: Test editing a Minispeak question
  As a teacher
  In order to be able to update my Minispeak question
  I need to edit them

  Background:
    Given the following "users" exist:
      | username |
      | teacher  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype       | name                        | template    |
      | Test questions   | minispeak | Minispeak for editing | two_of_four |
      | Test questions   | minispeak | Single choice for editing   | one_of_four |

  Scenario: Edit a Minispeak question with multiple response (checkboxes)
    When I am on the "Minispeak for editing" "core_question > edit" page logged in as teacher
    And I set the following fields to these values:
      | Question name | |
    And I press "id_submitbutton"
    And I should see "You must supply a value here."
    And I set the following fields to these values:
      | Question name | Edited Minispeak name |
    And I press "id_submitbutton"
    Then I should see "Edited Minispeak name"

  Scenario: Edit a Minispeak question with single response (radio buttons)
    When I am on the "Single choice for editing" "core_question > edit" page logged in as teacher
    And I set the following fields to these values:
      | Question name | Edited Single choice name |
    And I press "id_submitbutton"
    Then I should see "Edited Single choice name"
