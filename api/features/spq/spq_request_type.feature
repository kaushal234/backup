Feature: Test SPQ request types API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\SPQ\RequestType" should only be available for intranet user

  Scenario: Request all request types
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/request_types"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spq/request_type/schemas/request_types.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\SPQ\RequestType" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"

  Scenario: Request a single request type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/request_types/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spq/request_type/schemas/request_type.json"
