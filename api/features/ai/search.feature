Feature: Test search endpoints of AI API
  Scenario: Search through AI without being authenticated should not be allowed
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/search/intranet?query=test"
    Then the response status code should be 401
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/search/dms?query=test"
    Then the response status code should be 401
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/search/toc?query=test"
    Then the response status code should be 401

  Scenario: Query filter is mandatory for these routes
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/search/intranet"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Filter query is mandatory on this route."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/search/dms"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Filter query is mandatory on this route."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/search/toc"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Filter query is mandatory on this route."

  Scenario: Search through AI should be accessible only for intranet users
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/search/intranet?query=test"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/search/intranet?query=test"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/search/dms?query=test"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/search/dms?query=test"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/search/toc?query=test"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/search/toc?query=test"
    Then the response status code should be 403

  Scenario: Search in intranet
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/search/intranet?query=toto"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/search/schemas/search.json"

  Scenario: Search in toc
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/search/toc?query=toto"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/search/schemas/search.json"

  Scenario: Search in dms
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/search/dms?query=toto"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/search/schemas/search.json"