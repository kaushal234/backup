Feature: MOM

  Scenario: As an anonymous user, test that i'm not allowed to see meeting pages
    When I go to "/meetings"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/meetings/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see meeting pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/meetings"
    Then the response status code should be 200
    And The module should be "MOM"
    And I should see "Last 10 meeting"
    When I go to "/meetings/1/show"
    Then the response status code should be 200
    And I should see "Meeting"
    And I should see "Tasks"
    And I should see "Logs"
    And I should see "Followers"
    And I should see "Files"

  Scenario: As a basic user, test that i'm allowed to created a meeting
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/meetings/add"
    Then the response status code should be 200

  @javascript
  Scenario: Only allowed people can access a confidential meeting
    Given I authenticate as "user-qam@tld.fr" with "P@ssw0rd15chars"
    Then I go to "/meetings/6/show"
    Then I should be on "/account"

  Scenario: Anybody can see non confidential meeting
    Given I authenticate as "user-psm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/meetings/1/show"
    Then the response status code should be 200

  Scenario: As the creator of the meeting, test that i can edit my meeting
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/meetings/1/edit"
    Then the response status code should be 200
    And I should see "Type English"
    And I should see "loser"

  Scenario: As the supervisor of the creator of the meeting, test that i can edit meeting
    Given I authenticate as "user-evp@tld.fr" with "P@ssw0rd15chars"
    When I go to "/meetings/6/edit"
    Then the response status code should be 200

  Scenario: Not allowed people can't edit a meeting
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/meetings/1/edit"
    Then I should not be on "/meetings/1/edit"
    And I should see "You do not have permissions"

  Scenario: Not allowed people can't duplicate a meeting
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/meetings/1/duplicate"
    Then I should not be on "/meetings/1/duplicate"
    And I should see "You do not have permissions"

  Scenario: Not allowed people can't change the status, delete a meeting or create a new task for this meeting
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/meetings/1/show"
    Then the response status code should be 200
    And I should not see an "a[href$='/meetings/1/status/RELEASED']" element
    And I should not see an "a[href$='/meetings/1/delete']" element
    And I should not see an "a[href$='/meetings/1/new-task']" element

  Scenario: Not allowed people can't change the status, delete a meeting or create a new task for this meeting
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/meetings/1/status/RELEASED"
    Then I should not be on "/meetings/1/status/RELEASED"
    And I should see "You do not have permissions"

  @javascript
  Scenario: : The creator of a meeting can add a task to a meeting
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/meetings/6/new-task"
    And I wait until I see "NEW TASK"
    Then I fill in "meeting_action_description" with "You will survive"
    Then I fill in "meeting_action_dueDate" with "11/22/2099"
    Then I fill in "meeting_action_escalationTrigger" with "22"
    Then I check "meeting_action_internal"
    Then I select "/people/53" from "meeting_action[assignee]"
    Then I select "/people/54" from "meeting_action[cc][]"
    And I press "Submit"
    Then I wait until I see "A new task has been created"
    And I should be on "/meetings/6/show"
    And I click on the 1st "a[href='#action']" element
    And I should see "You will survive"

  @javascript
  Scenario:  The creator of a meeting can't close it if all its tasks are not completed
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/meetings/6/show"
    And I wait until I see "MOM#6 - Six"
    When I go to "/meetings/6/status/RELEASED"
    And I wait until I see "RELEASED"
    Then I should be on "/meetings/6/show"
    When I go to "/meetings/6/status/CLOSED"
    And I wait until I see "Status CLOSED is not allowed. Reasons: Some actions of the meeting are not completed."
    Then I should be on "/meetings/6/show"

  Scenario: : The creator of a meeting can duplicate his meeting
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/meetings/6/duplicate"
    Then the response status code should be 200
    Then I fill in "meeting_duplicate_type_form[description]" with "This is a duplicated meeting"
    Then I fill in "meeting_duplicate_type_form[location]" with "Nante rue trafalgar"
    And I press "Submit"
    Then the response status code should be 200
    Then I should be on "/meetings/7/show"
    And I should see "Nante rue trafalgar"

  Scenario:  A meeting can be closed by its creator if all its tasks are completed
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/meetings/2/show"
    Then the response status code should be 200
    When I go to "/meetings/2/status/RELEASED"
    Then the response status code should be 200
    When I go to "/meetings/2/status/CLOSED"
    Then the response status code should be 200
    Then I should be on "/meetings/2/show"

  Scenario: A basic user can filter a meeting
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/meetings/search"
    And I select "winner" from "competitors[]"
    And I press "FILTER"
    Then I should be on "/meetings/search"
    And the response status code should be 200

  Scenario: Test the creator of a meeting can delete his meeting
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/meetings/6/show"
    Then the response should contain "/meetings/6/delete?_token="

  @javascript
  Scenario: As an allowed user, I can create a meeting with one punctual contact
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/meetings/add"
    Then I fill in rich textarea "meeting_type[description]" with "My meeting description"
    When I fill in the following:
      | meeting_type[title]         | My super meeting title  |
      | meeting_type[location]      | Paris                   |
      | meeting_type[meetingDate]   | 04/20/2122              |
    And I select "/sales/customers/10" from "meeting_type[customers][]"
    And I select "/sales/extranet_users/256" from "meeting_type[customerContacts][]"
    When I click on the 1st ".btn[data-action='form-collection#addCollectionElement']" element
    And I fill in "meeting_type[contacts][0][firstName]" with "John"
    And I fill in "meeting_type[contacts][0][lastName]" with "Doe"
    And I fill in "meeting_type[contacts][0][company]" with "ACME"
    And press "meeting_type[submit]"
    And I wait until I see "A meeting has been created"
    Then I should be on "/meetings/7/show"
    And I wait until I see "My super meeting title"

  @javascript
  Scenario: As an allowed user, I can edit a meeting with one punctual contact
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/meetings/7/edit"
    When I fill in the following:
      | meeting_type[title]         | My super meeting title not so super  |
    And I fill in "meeting_type[contacts][0][lastName]" with "DoeWithD"
    And press "meeting_type[submit]"
    And I wait until I see "The meeting has been updated successfully"
    Then I should be on "/meetings/7/show"
    And I wait until I see "My super meeting title not so super"
    And I wait until I see "DoeWithD"
