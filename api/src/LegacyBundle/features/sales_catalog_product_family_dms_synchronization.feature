Feature: Test sales catalogue family dms double write API

  Scenario: Create a sales catalogue family dms in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/product_family_dms" with the body "tests/fixtures/json/sales/product_family_dms/dummies/post.json"
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "products_datasheets_dms"
    And a new row has been inserted in the legacy table "mod_logs"
    And the column "dms_id" from the "products_datasheets_dms" legacy table has been inserted with integer 1234
