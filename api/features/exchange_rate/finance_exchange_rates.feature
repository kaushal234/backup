Feature: Test exchange rates can be created and updated

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Finance\ExchangeRate" should only be available for intranet user

  Scenario: Exchange rates should be accessible to intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/exchange_rates"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/finance_exchange_rate/schemas/exchange_rates.json"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I send a "GET" request to "/exchange_rates?context[datetime_format]=Y-m&properties[currency][]=name&properties[]=applicatedOn"
    Then the response status code should be 200
    And the JSON node "hydra:member[0].currency.name" should be equal to "USD"
    And the JSON node "hydra:member[0].applicatedOn" should be equal to "1990-07"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Finance\ExchangeRate" is exposed on the API
    Then the filter "currency" should be available and its type should be "string"
    And the filter "currency.name" should be available and its type should be "string"
    And the filter "type" should be available and its type should be "string"
    And the filter "applicatedOn[before]" should be available and its type should be "DateTimeInterface"
    And the filter "order[applicatedOn]" should be available and its type should be "string"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "order[type]" should be available and its type should be "string"
    And the filter "order[currency.name]" should be available and its type should be "string"
    And the filter "context[datetime_format]" should be available and its type should be "string"
    And the filter "properties[currency]" should be available and its type should be "string"
    And the filter "properties[]" should be available and its type should be "string"

  Scenario: Get reports for exchange rates by sso name
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/exchange_rates;x=applicatedOn;y=currency.name?options[type]=endOfMonth&options[currency]=EUR"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON should be valid according to this schema:
    """
    {
      "type": "object",
      "properties": {
        "xTotals": {
          "type": "object",
          "patternProperties": {
            "^\\d{4}\\-\\d{2}$": {"type": "number"}
           },
           "minProperties": 1
        }
      }
    }
    """

  Scenario: Exchange rate detail should be accessible to intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/exchange_rates/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/finance_exchange_rate/schemas/exchange_rate.json"

  Scenario: Create an Exchange rate without permission should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/exchange_rates" with the body "tests/fixtures/json/finance_exchange_rate/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create an invalid exchange rate without should trigger business validation rules
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/exchange_rates" with body:
    """
      {
        "applicatedOn": "2019-01-07",
        "type": "GOD",
        "rate": "-254.949",
        "currency": "/finance/currencies/7"
      }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "applicatedOn"
    And the JSON node "violations[0].message" should be equal to "This value must be the first day of the month."
    And the JSON node "violations[1].propertyPath" should be equal to "type"
    And the JSON node "violations[1].message" should be equal to "The value you selected is not a valid choice."
    And the JSON node "violations[2].propertyPath" should be equal to "rate"
    And the JSON node "violations[2].message" should be equal to "This value should be 0 or more."

  Scenario: Create an Exchange rate
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/exchange_rates" with the body "tests/fixtures/json/finance_exchange_rate/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/finance_exchange_rate/schemas/exchange_rate.json"

  Scenario: Can't create duplicated rates of the following properties : applicatedOn, type and currency
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/exchange_rates" with the body "tests/fixtures/json/finance_exchange_rate/dummies/post.json"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "type"
    And the JSON node "violations[0].message" should be equal to "There is already a rate of this type on this currency for this month."

  Scenario: Update a Exchange rate without permission should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/exchange_rates/1" with the body "tests/fixtures/json/finance_exchange_rate/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a Exchange rate
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/exchange_rates/1" with the body "tests/fixtures/json/finance_exchange_rate/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/finance_exchange_rate/schemas/exchange_rate.json"

  Scenario: Update a Exchange rate with 5 number before comma and 8 number after comma
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/exchange_rates/1" with body:
    """
    {
      "applicatedOn": "2050-02-01",
      "type": "AVG",
      "rate": "12345.12345678",
      "currency": "/finance/currencies/7"
    }
    """
    Then the response status code should be 200
    And the JSON node "rate" should be equal to "12345.12345678"

  Scenario: Delete an exchange rate with attached lines allowing it
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/exchange_rates/2"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/exchange_rates/2"
    Then the response status code should be 204
