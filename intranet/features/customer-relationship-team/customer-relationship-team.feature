Feature: Customer Relationship Team

  Scenario: As an anonymous user, test that i'm not allowed to see customer relationship team pages
    When I go to "/sales/customer-relationship-teams"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, I can see customer relationship team pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customer-relationship-teams"
    Then the response status code should be 200
    And I should see "Last 10 CRT Created"
    And The module should be "CRT"
    And I should not see an "a[href$='/sales/customer-relationship-teams/add']" element
    When I go to "/sales/customer-relationship-teams/3/show"
    Then the response status code should be 200

  Scenario: As a superuser, I can see customer relationship team pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customer-relationship-teams"
    Then the response status code should be 200
    And I should see "Last 10 CRT Created"
    And I should see an "a[href$='/sales/customer-relationship-teams/add']" element
    Then I should see 10 "table.footable tbody tr" elements
    And the "table.footable tbody tr:nth-child(2) td:nth-child(2)" element should contain a text
    When I go to "/sales/customer-relationship-teams/3/show"
    Then the response status code should be 200
    And I should see "Edit"
    And I should see "Duplicate"

  Scenario: As a superuser, test that i can delete a customer relationship team
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customer-relationship-teams/5/delete"
    Then the response status code should be 200
    And I should be on "/sales/customer-relationship-teams"
    And I should see "The CRT has been deleted successfully"

