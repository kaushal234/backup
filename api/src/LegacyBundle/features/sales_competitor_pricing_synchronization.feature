Feature: Test competitor pricings double write API

  Scenario: Create a competitor pricing in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/competitor_pricings" with the body "tests/fixtures/json/sales/competitor_pricing/dummies/post.json"
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "cpr"
    And the column "competitor" from the "cpr" legacy table has been inserted with integer 215
    And a new row has been inserted in the legacy table "mod_logs"
    And the column "module" from the "mod_logs" legacy table has been inserted with string "CPR"

  Scenario: Update a competitor pricing in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/competitor_pricings/1" with the body "tests/fixtures/json/sales/competitor_pricing/dummies/put.json"
    Then the response status code should be 200
    And the column "parent_id" from the "cpr" legacy table has been updated with integer 6113
    And the column "qdate" from the "cpr" legacy table has been updated with string "2100-12-25"
    And the column "competitor" from the "cpr" legacy table has been updated with integer 216
    And the column "model" from the "cpr" legacy table has been updated with string "PATA"
    And the column "options" from the "cpr" legacy table has been updated with string "Jantes sport"
    And the column "qty" from the "cpr" legacy table has been updated with integer 54
    And the column "price" from the "cpr" legacy table has been updated with number "560.12"
    And the column "currency" from the "cpr" legacy table has been updated with string "EUR"
    And the column "exchange_rate" from the "cpr" legacy table has been updated with number "3.14"
    And the column "inco_terms" from the "cpr" legacy table has been updated with string "FCA"
    And the column "inco_loc" from the "cpr" legacy table has been updated with string "she\'s my baby"
    And the column "markup_percent" from the "cpr" legacy table has been updated with integer 99
    And a new row has been inserted in the legacy table "mod_logs"
    And the column "module" from the "mod_logs" legacy table has been inserted with string "CPR"
