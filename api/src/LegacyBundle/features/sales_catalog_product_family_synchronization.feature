Feature: Test sales catalog product families double write API

  Scenario: Create a sales catalog product family in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/product_families" with the body "tests/fixtures/json/sales/product_family/dummies/post.json"
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "products_datasheets"
    And a new row has been inserted in the legacy table "mod_logs"

  Scenario: Update a sales catalog product family in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_families/8" with the body "tests/fixtures/json/sales/product_family/dummies/put.json"
    Then the response status code should be 200
    And the column "parent_id" from the "products_datasheets" legacy table has been updated with integer 28
    And the column "model" from the "products_datasheets" legacy table has been updated with string "Family Test 2"
    And the column "hidden" from the "products_datasheets" legacy table has been updated with integer 1
    And the column "public" from the "products_datasheets" legacy table has been updated with integer 0
    And the column "en" from the "products_datasheets" legacy table has been updated with string "Do you want a cup of tea ?"
    And the column "fr" from the "products_datasheets" legacy table has been updated with string "Ceci est une famille test."
    And the column "es" from the "products_datasheets" legacy table has been updated with string "Aye Pepito !"
    And the column "zh" from the "products_datasheets" legacy table has been updated with string "&#20050;&#20051;&#29699;"
    And the column "pt" from the "products_datasheets" legacy table has been updated with string "Fé le ménache !"
    And the column "de" from the "products_datasheets" legacy table has been updated with string "Deutsch Qualität"
    And the column "ja" from the "products_datasheets" legacy table has been updated with string "&#12363;&#12417;&#12399;&#12417;&#27874;&#12399;"
    And the column "ru" from the "products_datasheets" legacy table has been updated with string "&#1042;&#1086;&#1076;&#1082;&#1072;!"
    And a new row has been inserted in the legacy table "mod_logs"
