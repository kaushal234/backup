Feature: Test currencies

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Finance\Currency" should only be available for intranet user

  Scenario: Currencies should be accessible to intranet users
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "finance/currencies"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/finance_currency/schemas/currencies.json"
    # Default ordering
    And the JSON node "hydra:member[0].name" should be equal to the string "CAD"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Finance\Currency" is exposed on the API
    Then the filter "name" should be available and its type should be "string"

  Scenario: Currency detail should be accessible to intranet users
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "finance/currencies/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/finance_currency/schemas/currency.json"

  Scenario: Updating a currency is not possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "finance/currencies/1" with body:
    """
    {
       "name": "Simon"
    }
    """
    Then the response status code should be 405

  Scenario: Deleting a currency is not possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "finance/currencies/1"
    Then the response status code should be 405

  Scenario: Create a currency is not allowed for basic users
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "finance/currencies" with body:
    """
    {
       "name": "SIM"
    }
    """
    Then the response status code should be 403

  Scenario: Create a currency is allowed to super user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "finance/currencies" with body:
    """
    {
      "name": "SIMON"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to the string "name"
    And the JSON node "violations[0].message" should be equal to the string "This value should have exactly 3 characters."
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "finance/currencies" with body:
    """
    {
       "name": "SIM"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/finance_currency/schemas/currency.json"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "finance/currencies" with body:
    """
    {
       "name": "SIM"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "name"
    And the JSON node "violations[0].message" should contain "This value is already used."
