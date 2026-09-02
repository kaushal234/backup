Feature: Sales Forecasts can be filtered using some of their properties

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\SalesForecast" is exposed on the API
    Then the filter "masterSalesForecast" should be available and its type should be "string"
    And the filter "status" should be available and its type should be "string"
    And the filter "lastComment" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "product" should be available and its type should be "string"
    And the filter "product.family" should be available and its type should be "string"
    And the filter "product.financeFamily" should be available and its type should be "string"
    And the filter "product.family.productType" should be available and its type should be "string"
    And the filter "sso" should be available and its type should be "string"
    And the filter "sso.legacyId" should be available and its type should be "int"
    And the filter "tier" should be available and its type should be "string"
    And the filter "factory" should be available and its type should be "string"
    And the filter "asm" should be available and its type should be "string"
    And the filter "poster" should be available and its type should be "string"
    And the filter "equoteId" should be available and its type should be "string"
    And the filter "buyer" should be available and its type should be "string"
    And the filter "buyer.customerTypes.name" should be available and its type should be "string"
    And the filter "endUser.customerTypes.name" should be available and its type should be "string"
    And the filter "endUser" should be available and its type should be "string"
    And the filter "thirdParty" should be available and its type should be "string"
    And the filter "airport" should be available and its type should be "string"
    And the filter "delinquent" should be available and its type should be "bool"
    And the filter "customer" should be available and its type should be "string"
    And the filter "country" should be available and its type should be "string"
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "order[legacyId]" should be available and its type should be "string"
    And the filter "order[asm.lastname]" should be available and its type should be "string"
    And the filter "order[factory.name]" should be available and its type should be "string"
    And the filter "order[sso.name]" should be available and its type should be "string"
    And the filter "order[buyer.name]" should be available and its type should be "string"
    And the filter "order[endUser.name]" should be available and its type should be "string"
    And the filter "order[thirdParty.name]" should be available and its type should be "string"
    And the filter "order[product.name]" should be available and its type should be "string"
    And the filter "order[quantity]" should be available and its type should be "string"
    And the filter "order[status]" should be available and its type should be "string"
    And the filter "order[airport.name]" should be available and its type should be "string"
    And the filter "order[country.name]" should be available and its type should be "string"
    And the filter "order[tier.name]" should be available and its type should be "string"
    And the filter "order[delinquent]" should be available and its type should be "string"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "order[estimatedSaleDate]" should be available and its type should be "string"
    And the filter "order[successPercentage]" should be available and its type should be "string"
    And the filter "order[masterSalesForecast.id]" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"

  Scenario: Search Sales Forecasts
    Given I authenticate as the intranet user "user-chairman@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts?q=RIEN&context[no_headers]=true"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecasts.json"

  Scenario: Filter open Sales Forecasts (open status OR closed during the last month)
    Given I authenticate as the intranet user "user-chairman@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts?open=true"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecasts.json"

  Scenario: Filter Sales Forecasts by military customers
    Given I authenticate as the intranet user "user-chairman@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts?military=true"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecasts.json"
