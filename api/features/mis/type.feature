Feature: Test Type Entity

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\MIS\TroubleTicket\Type" should only be available for intranet user

  Scenario: Request all types as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/types"
    Then the response status code should be 403

  Scenario: Request a single application as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/types/1"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\Entity\MIS\TroubleTicket\Type" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"
    Then the filter "order[displayedOrder]" should be available and its type should be "string"
    Then the filter "type" should be available and its type should be "string"
    Then the filter "description" should be available and its type should be "string"

  Scenario: Request all types as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/types"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/type/schemas/types.json"

  Scenario: Request a single type as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/types/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis/type/schemas/type.json"

  Scenario: Update a type should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/types/1" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: Create a type should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/types" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: Delete a type should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/types/1"
    Then the response status code should be 405