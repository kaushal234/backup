Feature: Sales Forecasts can be created and updated

  Scenario: A PSM can't edit a Sales Forecast out of his factory
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/1" with the body "tests/fixtures/json/sales/sales_forecast/dummies/put.json"
    Then the response status code should be 403

  Scenario: A PSM can't comment a Sales Forecast out of his factory
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/sales/sales_forecasts/1",
      "message": "Hello world"
    }
    """
    Then the response status code should be 403

  Scenario: A PSM can only edit the factory of a Sales Forecast
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/16" with the body "tests/fixtures/json/sales/sales_forecast/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "masterSalesForecast.@id" should be equal to the string "/sales/master_sales_forecasts/6"
    And the JSON node "status" should be equal to the string "BUDGET"
    And the JSON node "lastComment" should be equal to the string "Abel, Yves and Ken touch the sky"
    And the JSON node "lastCommentedAt" should be newer than 1 minute ago
    And the JSON node "sso.@id" should be equal to the string "/locations/23"
    And the JSON node "factory.@id" should be equal to the string "/locations/29"
    And the JSON node "asm.@id" should be equal to the string "/people/31"
    And the JSON node "buyer.@id" should be equal to the string "/sales/customers/1"
    And the JSON node "endUser.@id" should be equal to the string "/sales/customers/32"
    And the JSON node "country.@id" should be equal to the string "/countries/11"
    And the JSON node "airport.@id" should be equal to the string "/airports/61"
    And the JSON node "product.@id" should be equal to the string "/sales/products/1"
    And the JSON node "quantity" should be equal to the number 12
    And the JSON node "estimatedSaleDate" should be equal to the string "2099-04-30T00:00:00-04:00"
    And the JSON node "customerSuccessPercentage" should be equal to the number 99
    And the JSON node "successPercentage" should be equal to the number 1
    And the JSON node "tier.@id" should be equal to the string "/emission_ratings/3"
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/16" with body:
    """
    {
      "factory": "/locations/30",
      "comment": "hello"
    }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/16"
    Then the JSON node "factory.@id" should be equal to the string "/locations/30"

  Scenario: An ASM can only edit some fields of a Sales Forecast
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/16" with the body "tests/fixtures/json/sales/sales_forecast/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "masterSalesForecast.@id" should be equal to the string "/sales/master_sales_forecasts/1"
    And the JSON node "status" should be equal to the string "DELAYED"
    And the JSON node "lastComment" should be equal to the string "Abel, Yves and Ken touch the sky"
    And the JSON node "sso.@id" should be equal to the string "/locations/23"
    And the JSON node "factory.@id" should be equal to the string "/locations/30"
    And the JSON node "asm.@id" should be equal to the string "/people/31"
    And the JSON node "buyer.@id" should be equal to the string "/sales/customers/2"
    And the JSON node "endUser.@id" should be equal to the string "/sales/customers/33"
    And the JSON node "country.@id" should be equal to the string "/countries/3"
    And the JSON node "airport.@id" should be equal to the string "/airports/62"
    And the JSON node "product.@id" should be equal to the string "/sales/products/2"
    And the JSON node "quantity" should be equal to the number 13
    And the JSON node "estimatedSaleDate" should be equal to the string "2100-04-30T00:00:00-04:00"
    And the JSON node "customerSuccessPercentage" should be equal to the number 100
    And the JSON node "successPercentage" should be equal to the number 2
    And the JSON node "tier.@id" should be equal to the string "/emission_ratings/4"
    And the JSON node "quote.quoteNumber" should be equal to the string "QU123456"
    And an update log should have been inserted on resource "/sales/sales_forecasts/16" with a changeset on the property "estimatedSaleDate" with values "2099-04", "2100-04"

  Scenario: An ASM can unlink a SFR from a Master SFR and link it through another SFR
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/16" with body:
    """
    {
      "masterSalesForecast": "/sales/master_sales_forecasts/1",
      "comment": "just to prove in this test that the master is the #1"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "masterSalesForecast.@id" should be equal to the string "/sales/master_sales_forecasts/1"
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/16/unlink"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "masterSalesForecast.@id" should be equal to the string "/sales/master_sales_forecasts/7"
    And no email should have been sent asynchronously
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/sales_forecasts/16/link" with body:
    """
    {
      "salesForecastToLink": "/sales/sales_forecasts/2"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "masterSalesForecast.@id" should be equal to the string "/sales/master_sales_forecasts/1"
    And no email should have been sent asynchronously

  Scenario: A ASM can't edit the factory of a Sales Forecast
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/16" with body:
    """
    {
      "factory": "/locations/5",
      "comment": "hello"
    }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-asm@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "GET" request to "/sales/sales_forecasts/16"
    Then the response status code should be 200
    And the JSON node "factory.@id" should be equal to the string "/locations/30"

  Scenario: A comment is mandatory when editing a Sales Forecast
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/16" with body:
    """
    {
      "factory": "/locations/5"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "comment"
    And the JSON node "violations[0].message" should contain "This value should not be blank"

  Scenario: As an ASM, I can't edit SFR with status other than opened statuses
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/12" with body:
    """
    {
      "comment": "T'as pas le droit cousin",
      "status": "ORDERED"
    }
    """
    Then the response status code should be 403

  Scenario: As MOO, I can edit SFR with status with any statuses
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/12" with body:
    """
    {
      "comment": "Toi t'as le droit",
      "status": "ORDERED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "ORDERED"

  Scenario: As an ASM, I can't re-open SFR
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/12" with body:
    """
    {
      "comment": "T'as toujours pas le droit, tu te fais du mal là",
      "status": "BUDGET"
    }
    """
    Then the response status code should be 403


  Scenario: As superuser, I can edit SFR with status with any statuses
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/12" with body:
    """
    {
      "comment": "Toi par contre t'as le droit",
      "status": "DELAYED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "DELAYED"

  Scenario: As user EVP, I can edit SFR with status with any statuses
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/12" with body:
    """
    {
      "comment": "Toi aussi t'as aussi le droit",
      "status": "LOST"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "LOST"

  Scenario: ASM can't delete a Sales Forecast
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/sales_forecasts/12"
    Then the response status code should be 403

  Scenario: EVP can delete a Sales Forecast
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/sales_forecasts/12"
    Then the response status code should be 204

  Scenario: EVP can delete another Sales Forecast
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/sales_forecasts/11"
    Then the response status code should be 204

  Scenario: EVP can delete a Sales Forecast and it will remove the master if it's empty
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/master_sales_forecasts/2"
    Then the response status code should be 404

  Scenario: EVP can delete a Sales Forecast and it will remove the FCR
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/forecast_closures/3"
    Then the response status code should be 404

  Scenario: EVP can delete a Sales Forecast and it will unassign the CPR
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/competitor_pricings/2"
    Then the response status code should be 200
    Then the JSON node "forecastClosure" should be null

  Scenario: Some fields of a Sales Forecast have special log property names
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/1" with body:
    """
    {
      "comment": "just checking the conversion of those properties in the logs",
      "customerSuccessPercentage": 3,
      "successPercentage": 3
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "customerSuccessPercentage" should be equal to the number 3
    And the JSON node "successPercentage" should be equal to the number 3
    And an update log should have been inserted on resource "/sales/sales_forecasts/1" with a changeset on the property "Customer Purchase Percentage"
    And no update log should have been inserted on resource "/sales/sales_forecasts/1" with a changeset on the property "customerSuccessPercentage"
    And an update log should have been inserted on resource "/sales/sales_forecasts/1" with a changeset on the property "Alvest Success Percentage"
    And no update log should have been inserted on resource "/sales/sales_forecasts/1" with a changeset on the property "successPercentage"
    And no update log should have been inserted on resource "/sales/sales_forecasts/1" with a changeset on the property "lastComment"
