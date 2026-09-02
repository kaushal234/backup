Feature: Forecast Closures can be filtered

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Sales\ForecastClosure" should only be available for intranet user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\ForecastClosure" is exposed on the API
    Then the filter "status" should be available and its type should be "string"
    And the filter "reason" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "salesForecast" should be available and its type should be "string"
    And the filter "competitor" should be available and its type should be "string"
    And the filter "salesForecast.buyer" should be available and its type should be "string"
    And the filter "salesForecast.endUser" should be available and its type should be "string"
    And the filter "salesForecast.sso" should be available and its type should be "string"
    And the filter "salesForecast.factory" should be available and its type should be "string"
    And the filter "salesForecast.asm" should be available and its type should be "string"
    And the filter "salesForecast.buyer.country" should be available and its type should be "string"
    And the filter "salesForecast.product" should be available and its type should be "string"
    And the filter "salesForecast.product.family.productType" should be available and its type should be "string"
    And the filter "salesForecast.status" should be available and its type should be "string"
    And the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "salesForecast.estimatedSaleDate[after]" should be available and its type should be "DateTimeInterface"
    And the filter "salesForecast.estimatedSaleDate[before]" should be available and its type should be "DateTimeInterface"
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "order[salesForecast.asm.lastname]" should be available and its type should be "string"
    And the filter "order[salesForecast.id]" should be available and its type should be "string"
    And the filter "order[salesForecast.sso.name]" should be available and its type should be "string"
    And the filter "order[salesForecast.product.name]" should be available and its type should be "string"
    And the filter "order[salesForecast.factory.name]" should be available and its type should be "string"
    And the filter "order[salesForecast.buyer.name]" should be available and its type should be "string"
    And the filter "order[salesForecast.endUser.name]" should be available and its type should be "string"
    And the filter "order[salesForecast.quantity]" should be available and its type should be "string"
    And the filter "order[competitor.name]" should be available and its type should be "string"
    And the filter "order[orderedQuantity]" should be available and its type should be "string"
    And the filter "order[salesForecast.estimatedSaleDate]" should be available and its type should be "string"
    And the filter "order[status]" should be available and its type should be "string"
    And the filter "order[reason]" should be available and its type should be "string"
    And the filter "exists[competitor]" should be available and its type should be "bool"
    And the filter "q" should be available and its type should be "string"

  Scenario: Search Forecast Closures
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "sales/forecast_closures?q=RIEN"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/forecast_closure/schemas/forecast_closures.json"
