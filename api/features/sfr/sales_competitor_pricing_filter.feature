Feature: Competitor Pricings can be filtered

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\CompetitorPricing" is exposed on the API
    Then the filter "legacyId" should be available and its type should be "int"
    And the filter "forecastClosure" should be available and its type should be "string"
    And the filter "competitor" should be available and its type should be "string"
    And the filter "forecastClosure.status" should be available and its type should be "string"
    And the filter "forecastClosure.salesForecast" should be available and its type should be "string"
    And the filter "forecastClosure.salesForecast.product" should be available and its type should be "string"
    And the filter "order[id]" should be available and its type should be "string"
