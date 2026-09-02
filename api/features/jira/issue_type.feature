Feature: Test Jira Issue Type API
  Scenario: Request all Issue Types
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/jira/issue_types?projectId=10026"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/jira/issue_type/schemas/issue_types.json"

  Scenario: Request a single Priority
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/jira/issue_types/10039"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/jira/issue_type/schemas/issue_type.json"

  Scenario: Create, Update or delete an Issue should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/jira/issue_types/10039"
    Then the response status code should be 405
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/jira/issue_types/10039"
    Then the response status code should be 405
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/jira/issue_types"
    Then the response status code should be 405
