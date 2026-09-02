Feature: Test SupportLevel Entity

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\MIS\TroubleTicket\SupportLevel" should only be available for intranet user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\MIS\TroubleTicket\SupportLevel" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"
    Then the filter "order[level]" should be available and its type should be "string"
    Then the filter "order[name]" should be available and its type should be "string"
    Then the filter "name" should be available and its type should be "string"

  Scenario: Request all support levels as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/support_levels"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/support_level/schemas/support_levels.json"
    And the JSON node "hydra:totalItems" should be equal to 5

  Scenario: Request a single support level as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/support_levels/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/support_level/schemas/support_level.json"
    And the JSON node "level" should be equal to 0
    And the JSON node "name" should be equal to the string "Self-service"
    And the JSON node "description" should not be null

  Scenario: Create a support level should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/support_levels" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: Update a support level should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/support_levels/1" with body:
    """
    {}
    """
    Then the response status code should be 405
