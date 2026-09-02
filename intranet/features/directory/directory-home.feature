Feature: Directory / People

  Scenario: As an anonymous user, test that i'm not allowed to see the directory homepage
    When I go to "/directory"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see the directory homepage
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory"
    Then the response status code should be 200
    And The module should be "DIR"
    And I should see "Search"
    And I should see "Organisation Chart"
    And the response should contain "/directory/organisation/7"
    And the response should contain "/directory/organisation/2/4"
    When I go to "/directory/organisation/7"
    Then the response status code should be 200
    And I should see "rep, user"
