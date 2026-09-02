Feature: Test sales catalog products double write API

  Scenario: Create a sales catalog product in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/products" with the body "tests/fixtures/json/sales/product/dummies/post.json"
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "models"
    And the column "model" from the "models" legacy table has been inserted with string "Product test"
    And a new row has been inserted in the legacy table "mod_logs"

  Scenario: Update a sales catalog product in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/products/1" with the body "tests/fixtures/json/sales/product/dummies/put.json"
    Then the response status code should be 200
    And the column "family" from the "models" legacy table has been updated with string "Family test"
    And the column "model" from the "models" legacy table has been updated with string "Name test"
    And the column "hide" from the "models" legacy table has been updated with integer 1
    And the column "light" from the "models" legacy table has been updated with integer 1
    And the column "erpid" from the "models" legacy table has been updated with integer 404
    And the column "parent_id" from the "models" legacy table has been updated with integer 28
    And the column "finance_family" from the "models" legacy table has been updated with string "La FF"
    And the column "innovative_level" from the "models" legacy table has been updated with string "Introduction"
    And a new row has been inserted in the legacy table "mod_logs"
    And the column "model" from the "ccr" legacy table has been updated with string "Name test"
    And the column "model" from the "cor_prod" legacy table has been updated with string "Name test"
    And the column "model" from the "cpr" legacy table has been updated with string "Name test"
    And the column "model" from the "demerit" legacy table has been updated with string "Name test"
    And the column "model" from the "eap" legacy table has been updated with string "Name test"
    And the column "model" from the "gwf" legacy table has been updated with string "Name test"
    And the column "model" from the "manuals" legacy table has been updated with string "Name test"
    And the column "model" from the "meap" legacy table has been updated with string "Name test"
    And the column "model" from the "mod_models" legacy table has been updated with string "Name test"
    And the column "model" from the "models" legacy table has been updated with string "Name test"
    And the column "model" from the "ncr" legacy table has been updated with string "Name test"
    And the column "model" from the "nto_models" legacy table has been updated with string "Name test"
    And the column "model" from the "pip" legacy table has been updated with string "Name test"
    And the column "model" from the "products_datasheets" legacy table has been updated with string "Name test"
    And the column "model" from the "sb_coverage" legacy table has been updated with string "Name test"
    And the column "model" from the "sbs_lines" legacy table has been updated with string "Name test"
    And the column "model" from the "service" legacy table has been updated with string "Name test"
    And the column "model" from the "service_serials" legacy table has been updated with string "Name test"
    And the column "model" from the "sfr" legacy table has been updated with string "Name test"
    And the column "model" from the "sor_lines" legacy table has been updated with string "Name test"
    And the column "model" from the "warranty" legacy table has been updated with string "Name test"
