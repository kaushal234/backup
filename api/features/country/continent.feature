Feature: Test continents API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Continent" should only be available for intranet user

  Scenario: Request all continents
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/continents"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/continent/schemas/continents.json"

  Scenario: Request a single continent
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/continents/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/continent/schemas/continent.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Continent" is exposed on the API
    Then the filter "name" should be available and its type should be "string"
    And the filter "isoCode2" should be available and its type should be "string"
