Feature: Test Supplier Rankings criterias

  Scenario: Request all criterias for MLM User
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/criterias"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/criterias.json"

  Scenario: Request all criterias for Buyer User
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/criterias"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/criterias.json"

  Scenario: Request all criterias for CPO User
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/criterias"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/criterias.json"

  Scenario: Request a single criteria
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/criterias/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/criteria.json"
    And the JSON node "@id" should be equal to the string "/purchasing/supplier_ranking/criterias/1"
    And the JSON node "@type" should be equal to the string "Criteria"
    And the JSON node "name" should be equal to the string "Cost"
    And the JSON node "minTurnover" should be equal to the number 0

  Scenario: Request all criterias shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/criterias"
    Then the response status code should be 403

  Scenario: Request a criteria shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/criterias/1"
    Then the response status code should be 403
