Feature: Sales Forecasts are delinquent when no comments have been posted for one month and the estimated date is less than 3 months (or 2 months and between 3 and 6 months or 3 months and more than 6 months)

  Scenario: Sales Forecasts 9 is delinquent
    Given I authenticate as the intranet user "user-gch@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts?delinquent=true"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].@id" should be equal to "/sales/sales_forecasts/9"
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecasts.json"

  Scenario: An update on a Sales Forecast removes the delinquent status
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/9" with body:
    """
    {
      "comment": "hello"
    }
    """
    Then the response status code should be 200
    And the JSON node "delinquent" should be false
