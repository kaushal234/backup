Feature: Test sales forecasts double write API

  Scenario: Create a forecast closure in legacy database
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/forecast_closures" with the body "tests/fixtures/json/sales/forecast_closure/dummies/post_ordered.json"
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "fcr"
    And the column "status" from the "fcr" legacy table has been inserted with string "ORDERED"
    And the column "sfr_status" from the "fcr" legacy table has been inserted with string "ORDERED"
    And the column "status" from the "sfr" legacy table has been updated with string "ORDERED"
    And 9 new rows have been inserted in the legacy table "mod_logs"
    And the column "module" from the "mod_logs" legacy table has been inserted with string "FCR"

  Scenario: Update a forecast closure in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/forecast_closures/1" with body:
    """
    {
      "orderedQuantity": 42,
      "reason": "PERFORMANCE",
      "price": 42.13,
      "currency": "/finance/currencies/1",
      "competitor": "/sales/competitors/1"
    }
    """
    Then the response status code should be 200
    And the column "ordered_qty" from the "fcr" legacy table has been updated with integer 42
    And the column "reason" from the "fcr" legacy table has been updated with string "Technical / Equipment Performance"
    And the column "price" from the "fcr" legacy table has been updated with number "42.13"
    And the column "currency" from the "fcr" legacy table has been updated with string "USD"
    And the column "competitor" from the "fcr" legacy table has been updated with integer 215
    And a new row has been inserted in the legacy table "mod_logs"
    And the column "module" from the "mod_logs" legacy table has been inserted with string "FCR"
