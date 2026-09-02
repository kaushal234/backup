Feature: Test sales order double write API

  Scenario: Duplicate a sales order should perform duplication in other legacy table too
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/orders/2/duplicate" with body:
    """
      {
      }
    """
    Then the response status code should be 201
    # Index starts at 18894 and there are 40 sales orders in fixtures
    And the JSON node "legacyId" should be equal to the number 18934
    And a new row has been inserted in the legacy table "sor"
    And 2 new rows have been inserted in the legacy table "sor_lines"
    And the column "parent_id" from the "sor_lines" legacy table has been inserted with integer 18934
    And the column "dt_opened" from the "sor_lines" legacy table has been inserted with unquoted string "NOW()"
    And 2 insert queries has been executed on the legacy table "sor_units"
    And 2 insert queries has been executed on the legacy table "sor_opts"
    And 2 insert queries has been executed on the legacy table "mod_lists"

  Scenario: Delete a sales order with attached lines should perform delete in other legacy table too
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/orders/2"
    Then the response status code should be 204
    # And legacy deletions should be handled correctly
    And a row where "id" with value 18895 has been deleted from "sor" legacy table
    And a row where "id" with value 18819 has been deleted from "sor_lines" legacy table
    And a row where "parent_id" with value 18819 has been deleted from "sor_tran" legacy table
    And a row where "module" with value "SOL" has been deleted from "mod_lists" legacy table
    And a row where "parent_id" with value 18819 has been deleted from "mod_lists" legacy table
    And a row where "parent_id" with value 18819 has been deleted from "sor_opts" legacy table
    And the column "sor_uid" from the "service" legacy table has been updated with string ""
    And a row where "parent_id" with value 18819 has been deleted from "sor_units" legacy table
    And a row where "id" with value 18820 has been deleted from "sor_lines" legacy table
    And a row where "parent_id" with value 18820 has been deleted from "sor_tran" legacy table
    And a row where "parent_id" with value 18820 has been deleted from "mod_lists" legacy table
    And a row where "parent_id" with value 18820 has been deleted from "sor_opts" legacy table
    And a row where "parent_id" with value 18820 has been deleted from "sor_units" legacy table

  Scenario: Delete a sales order with attached lines after EVP_APPROVAL
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/orders/1"
    Then the response status code should be 422
    And 0 row have been deleted in the legacy table "sor"
    And 0 row have been deleted in the legacy table "sor_lines"
    And 0 row have been deleted in the legacy table "sor_tran"
    And 0 row have been deleted in the legacy table "mod_lists"
    And 0 row have been deleted in the legacy table "sor_opts"

  Scenario: Delete a sales order with attached lines breaking the backlog rule
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/orders/3"
    Then the response status code should be 422
    And 0 row have been deleted in the legacy table "sor"
    And 0 row have been deleted in the legacy table "sor_lines"
    And 0 row have been deleted in the legacy table "sor_tran"
    And 0 row have been deleted in the legacy table "mod_lists"
    And 0 row have been deleted in the legacy table "sor_opts"

  Scenario: Create a sales order in legacy database
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/orders" with the body "tests/fixtures/json/sales/orders/dummies/post.json"
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "sor"
    # /locations/23 as ERP 540
    And the column "bu" from the "sor" legacy table has been inserted with integer 540
    And the column "sso" from the "sor" legacy table has been inserted with integer 67
    And the column "juridical_entity_id" from the "sor" legacy table has been inserted with integer 1
    # /people/31 (asm) as legacy id 2378
    And the column "asm" from the "sor" legacy table has been inserted with integer 2378
    And the column "buyer_customer_id" from the "sor" legacy table has been inserted with integer 4074
    And the column "user_customer_id" from the "sor" legacy table has been inserted with integer 4105
    And the column "cu_nama" from the "sor" legacy table has been inserted with string "ASM WILL BE FIRED"
    And the column "status" from the "sor" legacy table has been inserted with string "PENDING"
    And 1 new rows have been inserted in the legacy table "mod_logs"
    And the column "module" from the "mod_logs" legacy table has been inserted with string "SOR"

  Scenario: Update a sales order in legacy database
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/orders/1" with the body "tests/fixtures/json/sales/orders/dummies/put_full.json"
    Then the response status code should be 200
    # /locations/31
    And the column "bu" from the "sor" legacy table has been updated with integer 420
    And the column "sso" from the "sor" legacy table has been updated with integer 75
    And the column "t_cuno" from the "sor" legacy table has been updated with string "BP001"
    # /juridical_locations/2, legacy id => 14
    And the column "juridical_entity_id" from the "sor" legacy table has been updated with integer 15
    And the column "eqno" from the "sor" legacy table has been updated with string "15645121"
    And the column "orno" from the "sor" legacy table has been updated with string "123,456,789"
    And the column "asm" from the "sor" legacy table has been updated with integer 2394
    And the column "buyer_customer_id" from the "sor" legacy table has been updated with integer 4106
    And the column "user_customer_id" from the "sor" legacy table has been updated with integer 4107
    And the column "cu_new" from the "sor" legacy table has been updated with string "Y"
    And the column "agnt_nama" from the "sor" legacy table has been updated with string "customer_5"
    And the column "cu_orno" from the "sor" legacy table has been updated with string "mael"
    And the column "src_xml" from the "sor" legacy table has been updated with string "<neo>I Know XML</neo>"
    And the column "note" from the "sor" legacy table has been updated with string "a bene"
    And the column "cu_nama" from the "sor" legacy table has been updated with string "HELICOPT\'AIR"
    And the column "status" from the "sor" legacy table has not been updated
    And 1 new rows have been inserted in the legacy table "mod_logs"
    And the column "module" from the "mod_logs" legacy table has been inserted with string "SOR"

  Scenario: Update a sales order status in legacy database
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/orders/1/status" with body:
    """
    {
      "status": "IN PROGRESS"
    }
    """
    Then the response status code should be 200
    And the column "status" from the "sor" legacy table has been updated with string "IN_PROGRESS"
