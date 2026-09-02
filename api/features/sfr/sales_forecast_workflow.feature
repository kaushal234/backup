Feature: Sales Forecasts can be created and updated

  Scenario: ASM can update status following the workflow
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/1/status" with body:
    """
    {
      "status": "IN_PROGRESS"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "IN_PROGRESS"
    And no email should have been sent asynchronously with subject matching pattern "/New comment added/"
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/1/status" with body:
    """
    {
      "status": "DELAYED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "DELAYED"
    And no email should have been sent asynchronously with subject matching pattern "/New comment added/"
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/1/status" with body:
    """
    {
      "status": "ORDERED"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Status ORDERED is not allowed"
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/1/status" with body:
    """
    {
      "status": "LOST"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Status LOST is not allowed"
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/1/status" with body:
    """
    {
      "status": "PARTIAL"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Status PARTIAL is not allowed"

  Scenario: EVP can't update the status and break the workflow of a Sales Forecast he can't access
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/5/status" with body:
    """
    {
      "status": "IN_PROGRESS"
    }
    """
    Then the response status code should be 403

  Scenario: Comment is mandatory for CANCELLED status
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/3/status" with body:
    """
    {
      "status": "CANCELLED"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "comment: This value should not be blank"
    And the JSON node "violations[0].propertyPath" should be equal to "comment"
    And the JSON node "violations[0].message" should contain "This value should not be blank"

  Scenario: EVP can update the status and break the workflow of a Sales Forecast he owns
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/3/status" with body:
    """
    {
      "status": "IN_PROGRESS"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "IN_PROGRESS"
    And no email should have been sent asynchronously with subject matching pattern "/New comment added/"
    And an email should have been sent asynchronously with subject matching pattern "/SFR#3 Updated/"
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/3/status" with body:
    """
    {
      "status": "BUDGET"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "BUDGET"
    And no email should have been sent asynchronously with subject matching pattern "/New comment added/"
    And an email should have been sent asynchronously with subject matching pattern "/SFR#3 Updated/"
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/3/status" with body:
    """
    {
      "comment": "CTRL + Z",
      "status": "CANCELLED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "CANCELLED"
    And no email should have been sent asynchronously with subject matching pattern "/New comment added/"
    And no email should have been sent asynchronously with subject matching pattern "/SFR#3 Updated/"
    And an email should have been sent asynchronously with subject matching pattern "/SFR#3 Cancelled/"
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/3/status" with body:
    """
    {
      "status": "BUDGET"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "BUDGET"
    And no email should have been sent asynchronously with subject matching pattern "/New comment added/"
    And an email should have been sent asynchronously with subject matching pattern "/SFR#3 Updated/"
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/3/status" with body:
    """
    {
      "status": "ORDERED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "ORDERED"
    And no email should have been sent asynchronously with subject matching pattern "/New comment added/"
    And no email should have been sent asynchronously with subject matching pattern "/SFR#3 Updated/"
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/3/status" with body:
    """
    {
      "status": "ORDER_CANCELLED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "ORDER_CANCELLED"
    And no email should have been sent asynchronously with subject matching pattern "/New comment added/"
    And an email should have been sent asynchronously with subject matching pattern "/SFR#3 Updated/"
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/3/status" with body:
    """
    {
      "status": "LOST"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "LOST"
    And no email should have been sent asynchronously with subject matching pattern "/New comment added/"
    And no email should have been sent asynchronously with subject matching pattern "/SFR#3 Updated/"
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/3/status" with body:
    """
    {
      "status": "PARTIAL"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "PARTIAL"
    And no email should have been sent asynchronously with subject matching pattern "/New comment added/"
    And no email should have been sent asynchronously with subject matching pattern "/SFR#3 Updated/"

  Scenario: SFR MOO can update the status and break the workflow
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/1/status" with body:
    """
    {
      "status": "IN_PROGRESS"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "IN_PROGRESS"
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/1/status" with body:
    """
    {
      "status": "BUDGET"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "BUDGET"
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/1/status" with body:
    """
    {
      "comment": "no",
      "status": "CANCELLED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "CANCELLED"
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/1/status" with body:
    """
    {
      "status": "BUDGET"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "BUDGET"

  Scenario: When a SFR leave a closed status, its FCRs are deleted
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/forecast_closures?salesForecast=/sales/sales_forecasts/4"
    Then the JSON node "hydra:member" should have 1 element
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/4/status" with body:
    """
    {
      "status": "IN_PROGRESS"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "IN_PROGRESS"
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/forecast_closures?salesForecast=/sales/sales_forecasts/4"
    Then the JSON node "hydra:member" should have 0 element

  Scenario: Cancelled status can be propagated to linked SFR
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/13/status" with body:
    """
    {
      "comment": "no",
      "cancellationPropagated": true,
      "status": "CANCELLED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "CANCELLED"
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/14"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "CANCELLED"
