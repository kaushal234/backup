Feature: Test Directory Position API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Directory\Position" should only be available for intranet user

  Scenario: Request all positions levels
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/position_levels"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_position_level/schemas/directory_position_levels.json"

  Scenario: Request a given position
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/position_levels/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_position_level/schemas/directory_position_level.json"
