Feature: Sales Forecasts sharing the same Master can be synchronized

  Scenario: Some fields updates are propagated when synchronization is activated
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/13" with body:
    """
    {
      "sso": "/locations/28",
      "factory": "/locations/30",
      "asm": "/people/32",
      "buyer": "/sales/customers/42",
      "endUser": "/sales/customers/33",
      "airport": "/airports/62",
      "product": "/sales/products/31",
      "quantity": 55,
      "estimatedSaleDate": "2100-04-19",
      "customerSuccessPercentage": 8,
      "successPercentage": 6,
      "tier": "/emission_ratings/4",
      "comment": "Abel, Yves and Ken touch the sky",
      "synchronized": true
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And an email should have been sent asynchronously with subject "customer_very_v; SFR Package Updated"
    And this asynchronous email should be sent from "user-moo-sfr@tld.fr"
    And this asynchronous email should contain "Linked SFR have been updated too"
    And no email should have been sent asynchronously with subject matching pattern "/SFR#\S+ Updated/"
    And no email should have been sent asynchronously with subject matching pattern "/New comment/"
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/14"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    #excluded from sync
    And the JSON node "lastComment" should be equal to the string "Abel, Yves and Ken touch the sky"
    #excluded from sync
    And the JSON node "sso.@id" should be equal to the string "/locations/23"
    #exluded from sync
    And the JSON node "factory.@id" should be equal to the string "/locations/29"
    And the JSON node "asm.@id" should be equal to the string "/people/32"
    And the JSON node "buyer.@id" should be equal to the string "/sales/customers/42"
    And the JSON node "endUser.@id" should be equal to the string "/sales/customers/33"
    And the JSON node "airport.@id" should be equal to the string "/airports/62"
    #excluded from sync
    And the JSON node "product.@id" should be equal to the string "/sales/products/1"
    And the JSON node "estimatedSaleDate" should be equal to the string "2100-04-30T00:00:00-04:00"
    And the JSON node "customerSuccessPercentage" should be equal to the number 8
    And the JSON node "successPercentage" should be equal to the number 6
    #excluded from sync
    And the JSON node "tier.@id" should be equal to the string "/emission_ratings/1"

  Scenario: Status update are propagated when synchronization is activated
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/13/status" with body:
    """
    {
      "synchronized": true,
      "status": "DELAYED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And an email should have been sent asynchronously with subject "customer_very_v; SFR Package Updated"
    And this asynchronous email should contain "Linked SFR have been updated too"
    And no email should have been sent asynchronously with subject matching pattern "/SFR#\S+ Updated/"
    And no email should have been sent asynchronously with subject matching pattern "/New comment/"
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/14"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And the JSON node "status" should be equal to the string "DELAYED"

  Scenario: Update some SFR Masters
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/11" with body:
    """
    {
      "masterSalesForecast": "/sales/master_sales_forecasts/2",
      "comment": "just to prepare the next test"
    }
    """
    Then the response status code should be 200

  Scenario: Synchronize without updating anything should not propagate data
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/11" with body:
    """
    {
      "comment": "Tirelipimpon sur le chihuahua",
      "synchronized": true
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    And no email should have been sent asynchronously with subject matching pattern "/SFR#\S+ Updated/"
    And no email should have been sent asynchronously with subject matching pattern "/New comment/"
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/12"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/sales_forecast/schemas/sales_forecast.json"
    #excluded from sync
    And the JSON node "lastComment" should be equal to the string "Tirelipimpon sur le chihuahua"
    And the JSON node "airport.@id" should not be equal to the string "/airports/111"
    And the JSON node "airport.@id" should be equal to the string "/airports/61"
