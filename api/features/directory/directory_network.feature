Feature: Test Directory Network API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Directory\Network" should only be available for intranet user

  Scenario: Request all networks
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/networks"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_network/schemas/directory_networks.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Directory\Network" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"

  Scenario: Request a given network
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/networks/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_network/schemas/directory_network.json"

