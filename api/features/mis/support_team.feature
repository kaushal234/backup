Feature: Test Unit Operational Status API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\MIS\SupportTeam" should only be available for "intranet" user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\MIS\SupportTeam" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"
    Then the filter "order[name]" should be available and its type should be "string"
    Then the filter "q" should be available and its type should be "string"

  Scenario: Request all support teams without being authenticated
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/support_teams"
    Then the response status code should be 401

  Scenario: Request all support teams
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/support_teams"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/support_team/schemas/support_teams.json"

  Scenario: Request a single Unit Operational Statuses
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/support_teams/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/support_team/schemas/support_team.json"

  Scenario: Create a support team should not be allowed for basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/support_teams" with body:
    """
    {
      "name": "TEST"
    }
    """
    Then the response status code should be 403

  Scenario: Create a support team should be allowed for mism user
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/support_teams" with body:
    """
    {
      "name": "TEST"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/support_team/schemas/support_team.json"

  Scenario: Update a support team should not be allowed for basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/support_teams/4" with body:
    """
    {
      "name": "TEST UPDATED"
    }
    """
    Then the response status code should be 403

  Scenario: Update a support team should be allowed for mism user
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/support_teams/4" with body:
    """
    {
      "name": "TEST UPDATED"
    }
    """
    Then the response status code should be 200
    And the JSON node "name" should be equal to the string "TEST UPDATED"
    And the JSON should be valid according to the schema "tests/fixtures/json/support_team/schemas/support_team.json"

  Scenario: Delete a support team should not be allowed for basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/support_teams/1"
    Then the response status code should be 403

  Scenario: Delete a support team should be allowed for cio
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/premises/1"
    Then the response status code should be 200
    And the JSON node "supportTeam" should not be null
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/support_teams/1"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/premises/1"
    Then the response status code should be 200
    And the JSON node "supportTeam" should be null
