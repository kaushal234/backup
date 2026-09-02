  Feature: Test Market intelligence Type entity

    Scenario: Resource should only be accessible for intranet users
      Given I add "Accept" header equal to "application/ld+json"
      Then the resource "App\Entity\Sales\MarketIntelligence\MarketIntelligenceType" should only be available for intranet user

    Scenario: Request all market intelligences types
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "sales/market_intelligence_types"
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence_type/schemas/market_intelligence_types.json"
      And the JSON node "hydra:totalItems" should be equal to 5

    Scenario: Market intelligence type should be accessible to basic user
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "sales/market_intelligence_types/1"
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence_type/schemas/market_intelligence_type.json"

    Scenario: Filters are declared on resource
      Given the class "App\Entity\Sales\MarketIntelligence\MarketIntelligenceType" is exposed on the API
      And the filter "order[name]" should be available and its type should be "string"

    Scenario: As a basic user, I can't create a market intelligence type
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "POST" request to "/sales/market_intelligence_types" with body:
      """
      {
        "name": "Test type"
      }
      """
      Then the response status code should be 403

    Scenario: As a super user, I can create a market intelligence type
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "POST" request to "/sales/market_intelligence_types" with body:
      """
      {
        "name": "Test type"
      }
      """
      Then the response status code should be 201
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence_type/schemas/market_intelligence_type.json"
      And the JSON node "name" should be equal to the string "Test type"

    Scenario: As a basic user, I can't edit a market intelligence type
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "PUT" request to "/sales/market_intelligence_types/6" with body:
      """
      {
        "name": "Test type updated"
      }
      """
      Then the response status code should be 403

    Scenario: As a super user, I can't edit a market intelligence type
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "PUT" request to "/sales/market_intelligence_types/6" with body:
      """
      {
        "name": "Test type updated"
      }
      """
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/sales/market_intelligence_type/schemas/market_intelligence_type.json"
      And the JSON node "name" should be equal to the string "Test type updated"

    Scenario: As a super user, I can't delete a market intelligence type
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-type" header equal to "application/ld+json"
      When I send a "DELETE" request to "/sales/market_intelligence_types/6"
      Then the response status code should be 405



