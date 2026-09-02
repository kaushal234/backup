Feature: Master Sales Forecasts can be created

  Scenario: Any user can create a Master Sales Forecast
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/master_sales_forecasts" with the body "tests/fixtures/json/sales/master_sales_forecast/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/master_sales_forecast/schemas/master_sales_forecast.json"
    And the JSON node "salesForecasts" should have 1 element

  Scenario: Any user can create a Master Sales Forecast
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/master_sales_forecasts" with the body "tests/fixtures/json/sales/master_sales_forecast/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/master_sales_forecast/schemas/master_sales_forecast.json"
    And the JSON node "salesForecasts" should have 1 element
