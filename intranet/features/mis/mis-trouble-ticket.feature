Feature: Trouble Ticket

  Scenario: As an anonymous user, test that i'm not allowed to see trouble ticket pages
    When I go to "/mis/trouble-tickets"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/mis/trouble-tickets/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see trouble ticket pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/trouble-tickets"
    Then the response status code should be 200
    And I should see an "a[href$='/mis/trouble-tickets/3/add-additional-owner']" element
    And I should see an "a[href$='/mis/trouble-tickets/3/follow']" element
    When I go to "/mis/trouble-tickets/1/show"
    Then the response status code should be 200
    And The module should be "TTS"
    And I should see "Details"
    And I should see "Status History"
    And I should not see an "a[href$='/mis/trouble-tickets/3/edit']" element
    And I should not see an "a[href$='/mis/trouble-tickets/3/request-information']" element
    And I should not see an "a[href$='/mis/trouble-tickets/3/transfer-to-jira']" element
    And I should not see an "a[href$='/mis/trouble-tickets/3/propose-solution']" element
    And I should not see an "a[href$='/mis/trouble-tickets/3/close']" element
    And I should not see an "a[href$='/mis/trouble-tickets/3/assign']" element
    And I should not see an "a[href$='/mis/trouble-tickets/3/reopen']" element

  Scenario: As basic user who want to send comment I can't if the comment filed is empty
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/trouble-tickets/1/comment"
    Then the response status code should be 200
    And I should see "Comment"
    And I fill in "trouble_ticket_comment_transfer[comment]" with ""
    And press "trouble_ticket_comment_transfer[submit]"
    Then I should be on "/mis/trouble-tickets/1/comment"
    And I should see "This value should not be blank."

  Scenario: As creator of TTS, test that i'm allowed to close and reopen it
    Given I authenticate as "user-psm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/trouble-tickets/2/show"
    Then the response status code should be 200
    And The module should be "TTS"
    And I should see "Details"
    And I should see an "a[href$='/mis/trouble-tickets/2/reopen']" element
    When I go to "/mis/trouble-tickets/2/reopen"
    Then the response status code should be 200
    And I should see "Reopen"
    And I fill in "trouble_ticket_comment_transfer[comment]" with "I need to reopen"
    And press "trouble_ticket_comment_transfer[submit]"
    Then I should be on "/mis/trouble-tickets/2/show"

  Scenario: Trouble tickets can be filtered
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/trouble-tickets?createdBy=/people/86&location[]=/locations/7&region[]=/regions/7&region[]=/regions/8&subDivision[]=/sub_divisions/3&assignee=/people/86&application[]=/mis/applications/9&application[]=/mis/applications/5&module[]=/modules/2&division[]=/divisions/1&createdAfter=04/02/2024&createdBefore=04/03/2024&type[]=/mis/types/1&status[]=PENDING&misAssignee=/people/86&premise[]=/premises/4&indiceFactor[]=IF 1"
    Then the response status code should be 200

  @javascript
  Scenario: As a basic user, test that i can access page to add trouble ticket
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/trouble-tickets/add"
    And I wait until I see "Open a trouble ticket"
    And I should be on "/mis/trouble-tickets/add"

  Scenario: As a basic user, I can add myself as an additional owner for a TTS
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/trouble-tickets/3/add-additional-owner"
    Then the response status code should be 200
    And I should be on "/mis/trouble-tickets/3/show"
    And I should see "Trouble Ticket updated successfully."
    When I go to "/mis/trouble-tickets"
    Then the response status code should be 200
    And I should not see an "a[href$='/mis/trouble-tickets/3/add-additional-owner']" element

  Scenario: As a basic user, I can follow a TTS
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/trouble-tickets/3/follow"
    Then the response status code should be 200
    And I should be on "/mis/trouble-tickets/3/show"
    And I should see "You are now following this Trouble Ticket."
    When I go to "/mis/trouble-tickets"
    Then the response status code should be 200
    And I should not see an "a[href$='/mis/trouble-tickets/3/follow']" element

  Scenario: As user MIS, I can comment, transfer, edit, assign, send to jira, propose solution or close a TTS
    Given I authenticate as "user-mis@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/trouble-tickets/3/show"
    Then the response status code should be 200
    And I should see an "a[href$='/mis/trouble-tickets/3/edit']" element
    And I should see an "a[href$='/mis/trouble-tickets/3/request-information']" element
    And I should see an "a[href$='/mis/trouble-tickets/3/transfer-to-jira']" element
    And I should see an "a[href$='/mis/trouble-tickets/3/close']" element
    And I should see an "a[href$='/mis/trouble-tickets/3/comment']" element
    And I should see an "a[href$='/mis/trouble-tickets/3/assign']" element
    When I go to "/mis/trouble-tickets/3/edit"
    Then the response status code should be 200
    And I should be on "/mis/trouble-tickets/3/edit"
    When I go to "/mis/trouble-tickets/3/close"
    Then the response status code should be 200
    And I should be on "/mis/trouble-tickets/3/close"
    When I go to "/mis/trouble-tickets/3/comment"
    Then the response status code should be 200
    And I fill in "trouble_ticket_comment_transfer[comment]" with "just a test comment"
    And press "trouble_ticket_comment_transfer[submit]"
    Then I should be on "/mis/trouble-tickets/3/show"
    And I should see "just a test comment"
    When I go to "/mis/trouble-tickets/3/request-information"
    Then the response status code should be 200
    And I fill in "trouble_ticket_comment_transfer[comment]" with "just a second comment to reply"
    And press "trouble_ticket_comment_transfer[submit]"
    Then I should be on "/mis/trouble-tickets/3/show"
    And I should see "just a second comment to reply"
    When I go to "/mis/trouble-tickets/3/assign"
    Then the response status code should be 200
    Then I should be on "/mis/trouble-tickets/3/show"
    And I should see "MIS Contact updated successfully."

  @authentication
  Scenario: As user MIS, I can access my preferences
    Given I authenticate as "user-mis@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/trouble-tickets/subscriptions"
    Then the response status code should be 200
    And I should see "No results"
    Then I click on element ID "trouble_ticket_setting_delete"
    Then I should be on "/mis/trouble-tickets/subscriptions"
    And the response status code should be 200
    And I should see "No results"
    Then I fill in "trouble_ticket_setting_status" with "PENDING"
    And I click on element ID "trouble_ticket_setting_submit"
    Then I should be on "/mis/trouble-tickets/subscriptions"
    And I should see "Subscriptions successfully updated."
    And I should see 4 "table.report-table tbody tr" elements

  @javascript
  Scenario: As user MIS, I can access report
    Given I authenticate as "user-mis@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/trouble-tickets/reports"
    Then I wait until I see "Filter"
    Then I select "/regions/7" from "mis_report[region][]"
    Then I select "/people/99" from "mis_report[misAssignee][]"
    Then I select "/mis/applications/13" from "mis_report[application][]"
    And I press "submit"
    Then I wait until I see "Last 12 months TTS satisfaction"
    And I should be on "/mis/trouble-tickets/reports"

  Scenario: As user MIS, I can access audit report
    Given I authenticate as "user-mis@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/trouble-tickets/audit"
    Then the response status code should be 200
    Then I select "ALVEST" from "trouble_ticket_audit_filter[region][]"
    Then I select "Incident" from "trouble_ticket_audit_filter[type]"
    Then I select "Extranet" from "trouble_ticket_audit_filter[application][]"
    And I press "FILTER"
    Then I should be on "/mis/trouble-tickets/audit"

  @javascript
  Scenario: As user MIS, I can access audit report and see all the reports
    Given I authenticate as "user-mis@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/trouble-tickets/audit"
    Then I select "Incident" from "trouble_ticket_audit_filter[type]"
    Then I fill in "trouble_ticket_audit_filter[createdAfter]" with "01/01/2024"
    And I press "FILTER"
    And I wait until I see "AUDIT BY FILTER (IN DAYS)"
    And I wait until I see "All TTS, any status, sorted by month of creation"
    And I wait until I see "TTS closed and still opened, sorted by month of creation"
    And I wait until I see "TTS sorted by month of closure"
    Then I should be on "/mis/trouble-tickets/audit"

  Scenario: As assignee of trouble ticket I should be able to comment and navigate to the next task or trouble ticket
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks"
    Then the response status code should be 200
    When I go to "/mis/trouble-tickets/3/show"
    And I should see an "a[href$='/mis/trouble-tickets/3/comment']" element
    When I go to "/mis/trouble-tickets/3/comment"
    And I should see "1 / 8"
    Then I fill in "trouble_ticket_comment_transfer[comment]" with "test01"
    And I press "trouble_ticket_comment_transfer[nextTask]"
    Then I should be on "/tasks/9/show"
    And I should see "2 / 8"

  Scenario: As basic user I should not have access to "Assign to me" button
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks"
    Then the response status code should be 200
    When I go to "/mis/trouble-tickets/8/show"
    Then the response status code should be 200
    Then I should not see an "select[id='mis_assignee_choice_assignee']" element

  Scenario: As mis user I should have access to "Assign to me" button
    Given I authenticate as "user-mis@tld.fr" with "P@ssw0rd15chars"
    When I go to "/tasks"
    Then the response status code should be 200
    When I go to "/mis/trouble-tickets/8/show"
    Then the response status code should be 200
    And I should see "Assign to me"
    And I should see an "select[id='mis_assignee_choice']" element

  Scenario: As a basic user, I can create a trouble ticket
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/trouble-tickets/add"
    Then the response status code should be 200
    And I select "/modules/2" from "trouble_ticket[module]"
    And I select "/applications/11" from "trouble_ticket[application]"
    And I select "Incident" from "trouble_ticket[typeCategory]"
    And I select "/mis/types/1" from "trouble_ticket[type]"
    And I fill in "trouble_ticket[url]" with "https://example.test/issue"
    And I fill in "trouble_ticket[shortDescription]" with "Behat created ticket"
    And I fill in "trouble_ticket[description]" with "Created from a Behat scenario"
    And I press "submit"
    Then the response status code should be 200
    And I should see "Trouble Ticket created successfully."

  Scenario: As user MIS, I can edit a trouble ticket
    Given I authenticate as "user-mis@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/trouble-tickets/3/edit"
    Then the response status code should be 200
    And I should be on "/mis/trouble-tickets/3/edit"
    And I fill in "trouble_ticket[shortDescription]" with "Updated by Behat"
    And I fill in "trouble_ticket[comment]" with "Behat edit reason"
    And I press "submit"
    Then I should be on "/mis/trouble-tickets/3/show"
    And I should see "Trouble Ticket updated successfully."
    Given I authenticate as "user-mis@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/trouble-tickets/3/show"
    Then the response status code should be 200
    And I should see "Behat edit reason"
    And I should see "Updated by Behat"

  Scenario: As a basic user I cannot see the assignee or status fields on edit
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/trouble-tickets/1/edit"
    And I should not see an "select[id='trouble_ticket_status']" element
    And I should not see an "select[id='trouble_ticket_assignee']" element

  Scenario: As MISM User, the edit form shows the assignee and status fields to it and the fields can be edited
    Given I authenticate as "user-mism@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/trouble-tickets/1/edit"
    Then the response status code should be 200
    And I should see an "select[id='trouble_ticket_status']" element
    And I should see an "select[id='trouble_ticket_assignee']" element
    And I select "PENDING" from "trouble_ticket[status]"
    And I fill in "trouble_ticket[comment]" with "Behat status change"
    And I press "submit"
    Then I should be on "/mis/trouble-tickets/1/show"
    And I should see "Trouble Ticket updated successfully."