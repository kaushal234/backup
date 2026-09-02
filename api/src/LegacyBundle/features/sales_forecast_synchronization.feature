Feature: Test sales forecasts double write API

  Scenario: Create a sales forecast in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/master_sales_forecasts" with the body "tests/fixtures/json/sales/master_sales_forecast/dummies/post.json"
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "sfr"
    And the column "sso_id" from the "sfr" legacy table has been inserted with integer 67
    And a new row has been inserted in the legacy table "sfr_master"
    And 2 new rows have been inserted in the legacy table "mod_logs"
    And the column "module" from the "mod_logs" legacy table has been inserted with string "SFR"

  Scenario: Update a sales forecast in legacy database
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/4" with the body "tests/fixtures/json/sales/sales_forecast/dummies/put_full.json"
    Then the response status code should be 200
    And the column "sfr_master_id" from the "sfr" legacy table has been updated with integer 3
    And the column "status" from the "sfr" legacy table has been updated with string "DELAYED"
    And the column "sso_id" from the "sfr" legacy table has been updated with integer 72
    And the column "erp_id" from the "sfr" legacy table has been updated with integer 74
    And the column "asm_id" from the "sfr" legacy table has been updated with integer 2379
    And the column "equote_id" from the "sfr" legacy table has been updated with string "12"
    And the column "buyer_customer_id" from the "sfr" legacy table has been updated with integer 4107
    And the column "cust_nama" from the "sfr" legacy table has been updated with string "HELICOPT\'AIR"
    And the column "cust_ctry" from the "sfr" legacy table has been updated with string "Kinder"
    And the column "user_customer_id" from the "sfr" legacy table has been updated with integer 4106
    And the column "third_party_id" from the "sfr" legacy table has been updated with integer 4107
    And the column "apc" from the "sfr" legacy table has been updated with string "CDG"
    And the column "model" from the "sfr" legacy table has been updated with string "Fixed"
    And the column "qty" from the "sfr" legacy table has been updated with integer 13
    And the column "year_id" from the "sfr" legacy table has been updated with string "2100"
    And the column "month_id" from the "sfr" legacy table has been updated with string "4"
    And the column "cust_pur_pc" from the "sfr" legacy table has been updated with integer 100
    And the column "tld_succ_pc" from the "sfr" legacy table has been updated with integer 2
    And the column "eng_tier" from the "sfr" legacy table has been updated with string "Tier 4"
    And the column "price" from the "sfr" legacy table has been updated with integer 17
    And 5 new rows have been inserted in the legacy table "mod_logs"
    And the column "module" from the "mod_logs" legacy table has been inserted with string "SFR"

  Scenario: Update a sales forecast status in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/4/status" with body:
    """
    {
      "status": "BUDGET"
    }
    """
    Then the response status code should be 200
    And the column "status" from the "sfr" legacy table has been updated with string "BUDGET"
