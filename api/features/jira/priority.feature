Feature: Test Jira Priority API
  Scenario: Request all Priorities
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/jira/priorities"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/jira/priority/schemas/priorities.json"

  Scenario: Request a single Priority
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/jira/priorities/3"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/jira/priority/schemas/priority.json"

  Scenario: Create, Update or delete an Issue should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/jira/priorities/3"
    Then the response status code should be 405
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/jira/priorities/3"
    Then the response status code should be 405
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/jira/priorities"
    Then the response status code should be 405
