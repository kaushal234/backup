Feature: Test finance family pricing module API

  Scenario: Finance family pricings should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/finance_family_pricings"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/finance_family_pricings"
    Then the response status code should be 403

  Scenario: As a basic user I can't see all finance family pricings
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/finance_family_pricings"
    Then the response status code should be 403

  Scenario: As a superuser I can see all finance family pricings
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/finance_family_pricings"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/finance_family_pricing/schemas/finance_family_pricings.json"

  Scenario: As CFO I can see all finance family pricings
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/finance_family_pricings"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/finance_family_pricing/schemas/finance_family_pricings.json"

  Scenario: As FC I can see all finance family pricings
    Given I authenticate as the intranet user "user-fc@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/finance_family_pricings"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/finance_family_pricing/schemas/finance_family_pricings.json"

  Scenario: As a basic user, I can't request one finance family pricing
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/finance_family_pricings/1"
    Then the response status code should be 403

  Scenario: As a superuser, I can request one finance family pricing
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/finance_family_pricings/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/finance_family_pricing/schemas/finance_family_pricing.json"

  Scenario: As CFO, I can request one finance family pricing
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/finance_family_pricings/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/finance_family_pricing/schemas/finance_family_pricing.json"

  Scenario: As FC, I can request one finance family pricing
    Given I authenticate as the intranet user "user-fc@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/finance_family_pricings/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/finance_family_pricing/schemas/finance_family_pricing.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Finance\FinanceFamilyPricing" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"
    And the filter "order[updatedAt]" should be available and its type should be "string"
    And the filter "order[averagePrice]" should be available and its type should be "string"
    And the filter "order[averageMargin]" should be available and its type should be "string"
    And the filter "financeFamily" should be available and its type should be "string"
    And the filter "sso" should be available and its type should be "string"
    And the filter "factory" should be available and its type should be "string"

