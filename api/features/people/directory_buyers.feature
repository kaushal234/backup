Feature: Test buyers API

  Scenario: Request all buyers as intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/buyers"
    Then the response status code should be 403

  Scenario: Request all buyers as evendors user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/buyers"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_people/schemas/directory_peoples_buyer.json"
    And the JSON node "hydra:member" should have 1 element
    Then the JSON node "hydra:member[0].email" should be equal to the string "alize.robert@tld-europe.com"
    And the JSON node "hydra:member[0].address" should not exist
    Then the JSON node "hydra:member[0].premise" should exist
    