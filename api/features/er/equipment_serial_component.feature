Feature: Test Equipment Serial Components API
  Scenario: Filters are declared on resource
    Given the class "App\Entity\Support\Component" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"

  Scenario: Request all Serial Components without being authenticated
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_serial_components"
    Then the response status code should be 401

  Scenario: Request all Serial Components
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_serial_components"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_serial_component/schemas/equipment_serial_components.json"

  Scenario: Request a single Serial Component
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_serial_components/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_serial_component/schemas/equipment_serial_component.json"