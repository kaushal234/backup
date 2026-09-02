Feature: Test Quote can be created and updated

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\Quote" is exposed on the API
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "order[quoteNumber]" should be available and its type should be "string"
    And the filter "quoteNumber" should be available and its type should be "string"

  Scenario: A user can use the simple search on quotes
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/quotes?q=456"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/quotes/schemas/quotes.json"

  Scenario: Request all quotes without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/quotes"
    Then the response status code should be 401

  Scenario: Request a single quote without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/quotes/1"
    Then the response status code should be 401

  Scenario: Quotes should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/quotes"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/quotes/1"
    Then the response status code should be 403

  Scenario: Quotes should be accessible to user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/quotes"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/quotes/schemas/quotes.json"

  Scenario: Single Quote should be accessible to user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/quotes/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/quotes/schemas/quote.json"

  Scenario: Delete a quote
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/quotes/1"
    Then the response status code should be 204

  Scenario: Quote can't be posted by intranet users
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/quotes" with parameters:
      | key             | value  |
      | xml             | @quote.xml |
    Then the response status code should be 403

  Scenario: Quote can't be posted by XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/quotes" with parameters:
      | key             | value  |
      | xml             | @quote.xml |
    Then the response status code should be 403

  Scenario: Quote can't be posted by an Authorized App without the correct feature
    Given I authenticate as the authorized application "pio"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/quotes" with parameters:
      | key             | value  |
      | xml             | @quote.xml |
    Then the response status code should be 403

  Scenario: Quote can be posted by an Authorized App with the correct feature
    Given I authenticate as the authorized application "ION"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/quotes" with parameters:
      | key             | value  |
      | xml             | @quote.xml |
    Then the response status code should be 201

  Scenario: Add a quote and update it
    Given I authenticate as the authorized application "ION"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/quotes" with parameters:
      | key             | value  |
      | xml             | @quote_to_update.xml |
    Then the response status code should be 201
    And the JSON node "xml" should contain "NUMBER_TO_UPDATE"
    Then I authenticate as the authorized application "ION"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    And I send a "POST" request to "/sales/quotes" with parameters:
      | key             | value  |
      | xml             | @quote_updated.xml |
    Then the response status code should be 201
    And the JSON node "xml" should contain "<Test>TEST updated</Test>"
