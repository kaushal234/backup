Feature: Test phones API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Directory\Phone" should only be available for intranet user

  Scenario: Request a single phone without authentication
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/phones/2"
    Then the response status code should be 401

  Scenario: Request all phones
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/phones"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_phone/schemas/directory_phones.json"

  Scenario: Request a single phone
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/phones/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_phone/schemas/directory_phone.json"
