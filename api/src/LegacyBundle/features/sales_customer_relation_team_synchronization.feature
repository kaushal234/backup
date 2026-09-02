Feature: Test customer relationship team double write API

  Scenario: Create a customer relationship team double write in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/customer_relationship_teams" with the body "tests/fixtures/json/sales/customer_relationship_team/dummies/post.json"
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "customers_crt"
    # 2 row, CRT and creation of task
    And 2 new rows have been inserted in the legacy table "mod_logs"

  Scenario: Update a customer relationship team double write in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "PUT" request to "/sales/customer_relationship_teams/1" with the body "tests/fixtures/json/sales/customer_relationship_team/dummies/put.json"
    Then the response status code should be 200
    # /sales/customers/2 legacy Id must be 4075
    And the column "customer_id" from the "customers_crt" legacy table has been updated with integer 4075
    # /people/2 legacyId = 2348
    And the column "sales_rep_id" from the "customers_crt" legacy table has been updated with integer 2548
    And the column "parts_rep_id" from the "customers_crt" legacy table has been updated with integer 2549
    And the column "services_rep_id" from the "customers_crt" legacy table has been updated with integer 2550
    And the column "parts_location_id" from the "customers_crt" legacy table has been updated with integer 71
    And the column "services_location_id" from the "customers_crt" legacy table has been updated with integer 69
    And the column "erp_location_id" from the "customers_crt" legacy table has been updated with integer 72
    And the column "cuno" from the "customers_crt" legacy table has been updated with string "PANDA"
    And a new row has been inserted in the legacy table "mod_logs"

  Scenario: Update a customer relationship team double write in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "PUT" request to "/sales/customer_relationship_teams/1" with body:
    """
    {
      "salesRepresentative": null,
      "partsRepresentative": null,
      "serviceRepresentative": null,
      "partsLocation": null,
      "serviceLocation": null,
      "erpLocation": null
    }
    """
    Then the response status code should be 200
    And the column "sales_rep_id" from the "customers_crt" legacy table has been updated with integer 0
    And the column "parts_rep_id" from the "customers_crt" legacy table has been updated with integer 0
    And the column "services_rep_id" from the "customers_crt" legacy table has been updated with integer 0
    And the column "parts_location_id" from the "customers_crt" legacy table has been updated with integer 0
    And the column "services_location_id" from the "customers_crt" legacy table has been updated with integer 0
    And the column "erp_location_id" from the "customers_crt" legacy table has been updated with integer 0

  Scenario: Delete a customer relationship team double write in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "DELETE" request to "/sales/customer_relationship_teams/15"
    Then the response status code should be 204
    And the column "deleted_at" from the "customers_crt" legacy table has been updated
    And 0 rows have been deleted in the legacy table "customers_crt"

