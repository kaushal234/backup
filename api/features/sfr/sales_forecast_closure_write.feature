Feature: Forecast Closures can be created and updated following the same rules than the Sales Forecast they belong to

  Scenario: An ASM of a Sales Forecast can open a Forecast Closure and its status will be propagated to the Sales Forecast
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/forecast_closures" with the body "tests/fixtures/json/sales/forecast_closure/dummies/post_ordered.json"
    Then the response status code should be 201
    And no email should have been sent asynchronously
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/forecast_closure/schemas/forecast_closure.json"
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/11"
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "ORDERED"

  Scenario: When forecast closure is created as lost, quantity lost is propagated to sales forecast
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/forecast_closures" with body:
    """
    {
      "salesForecast": "/sales/sales_forecasts/11",
      "status": "LOST",
      "orderedQuantity": 1,
      "reason": "PRICE",
      "comment": "t'as pu me faire ça ?",
      "price": 12000,
      "currency": "/finance/currencies/2",
      "competitor": "/sales/competitors/2"
    }
    """
    Then the response status code should be 201
    And no email should have been sent asynchronously
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/forecast_closure/schemas/forecast_closure.json"
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/11"
    Then the response status code should be 200
    And the JSON node "quantity" should be equal to 1

  Scenario: A Forecast Closure can be created on an already closed Sales Forecast
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/forecast_closures" with the body "tests/fixtures/json/sales/forecast_closure/dummies/post_ordered.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/forecast_closure/schemas/forecast_closure.json"

  Scenario: An ASM can't open a Forecast Closure on a Sales Forecast he doesn't own
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/forecast_closures" with the body "tests/fixtures/json/sales/forecast_closure/dummies/post_ordered_invalid.json"
    Then the response status code should be 403

  Scenario: An ASM of a Sales Forecast has to open two PARTIAL Forecast Closure for the status to be propagated to the Sales Forecast
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/forecast_closures" with the body "tests/fixtures/json/sales/forecast_closure/dummies/post_partial_ordered.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/forecast_closure/schemas/forecast_closure.json"
    And no email should have been sent asynchronously
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/10"
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "BUDGET"
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/forecast_closures" with the body "tests/fixtures/json/sales/forecast_closure/dummies/post_partial_lost.json"
    Then the response status code should be 201
    And no email should have been sent asynchronously
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/forecast_closure/schemas/forecast_closure.json"
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/10"
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "PARTIAL"

  Scenario: An ASM of a Sales Forecast can edit a Forecast Closure
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/forecast_closures/1" with body:
    """
    {
      "price": 5.12
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/forecast_closure/schemas/forecast_closure.json"

  Scenario: An ASM can't edit a Forecast Closure on a Sales Forecast he doesn't own
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/forecast_closures/2" with body:
    """
    {
      "price": 5.12
    }
    """
    Then the response status code should be 403

  Scenario: Anybody can see Forecast Closures
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/forecast_closures"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/forecast_closure/schemas/forecast_closures.json"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/forecast_closures/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/forecast_closure/schemas/forecast_closure.json"

  Scenario: ASM can't delete a Forecast Closure
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/forecast_closures/4"
    Then the response status code should be 403

  Scenario: EVP can delete a Forecast Closure
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/forecast_closures/3"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/competitor_pricings/2"
    Then the response status code should be 200
    Then the JSON node "forecastClosure" should be null
