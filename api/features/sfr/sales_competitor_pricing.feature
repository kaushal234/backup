Feature: Competitor Pricings can be created, read, updated and created through the API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Sales\CompetitorPricing" should only be available for intranet user

  Scenario: A basic user can access all CPR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/competitor_pricings"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/competitor_pricing/schemas/competitor_pricings.json"

  Scenario: A basic user can access a single CPR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/competitor_pricings/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/competitor_pricing/schemas/competitor_pricing.json"

  Scenario: A basic user can't create a CPR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/competitor_pricings" with the body "tests/fixtures/json/sales/competitor_pricing/dummies/post.json"
    Then the response status code should be 403

  Scenario: An ASM can create a CPR
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/competitor_pricings" with the body "tests/fixtures/json/sales/competitor_pricing/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/competitor_pricing/schemas/competitor_pricing.json"

  Scenario: A basic user can't update a CPR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/competitor_pricings/1" with the body "tests/fixtures/json/sales/competitor_pricing/dummies/put.json"
    Then the response status code should be 403

  Scenario: An ASM can update a CPR
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/competitor_pricings/1" with the body "tests/fixtures/json/sales/competitor_pricing/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/competitor_pricing/schemas/competitor_pricing.json"

  Scenario: Price and currency are not mandatory, but if price is submitted, currency is mandatory
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/competitor_pricings" with body:
    """
    {
      "quotationDate": "2050-12-25",
      "competitor": "/sales/competitors/1"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/competitor_pricing/schemas/competitor_pricing.json"
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/competitor_pricings" with body:
    """
    {
      "quotationDate": "2050-12-25",
      "competitor": "/sales/competitors/1",
      "price": 12
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should contain "currency: Currency is mandatory if you entered a price"
    And the JSON node "violations[0].propertyPath" should be equal to "currency"

  Scenario: User Basic can't delete a Competitor Pricing
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/competitor_pricings/4"
    Then the response status code should be 403

  Scenario: ASM can delete a Competitor Pricing
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/competitor_pricings/4"
    Then the response status code should be 204

  Scenario: ASM Supervisor can delete a Competitor Pricing
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/competitor_pricings" with body:
    """
    {
      "quotationDate": "2050-12-25",
      "competitor": "/sales/competitors/1"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/competitor_pricings/5"
    Then the response status code should be 204
