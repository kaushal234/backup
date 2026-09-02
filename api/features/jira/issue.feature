Feature: Test Jira Issue API
  Scenario: Request all Issues should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/jira/trouble_ticket_issues"
    Then the response status code should be 405
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/jira/user_story_issues"
    Then the response status code should be 405

  Scenario: Request a single Issue
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/jira/trouble_ticket_issues/SP-3017"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/jira/issue/schemas/issue.json"

  Scenario: Create an Issue should be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/jira/trouble_ticket_issues" with body:
    """
    {
      "projectId": 10026,
      "troubleTicketId": 1,
      "summary": "test",
      "description": "test",
      "type": "/jira/issue_types/10039"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/jira/issue/schemas/issue.json"

  Scenario: Update or delete an Issue should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/jira/trouble_ticket_issues/SP-3017"
    Then the response status code should be 405
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/jira/trouble_ticket_issues/SP-3017"
    Then the response status code should be 405
