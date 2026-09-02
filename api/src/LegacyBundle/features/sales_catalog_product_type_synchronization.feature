Feature: Test sales catalog product type double write API

  Scenario: Create a sales catalog product type in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/product_types" with the body "tests/fixtures/json/sales/product_type/dummies/post.json"
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "products_categories"
    And a new row has been inserted in the legacy table "mod_logs"

  Scenario: Update a sales catalog product type in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/product_types/2" with the body "tests/fixtures/json/sales/product_type/dummies/put.json"
    Then the response status code should be 200
    And the column "en" from the "products_categories" legacy table has been updated with string "Test Type"
    And the column "type" from the "cor_prod" legacy table has been updated with string "Test Type"
    And the column "type" from the "service" legacy table has been updated with string "Test Type"
    And the column "type" from the "gwf" legacy table has been updated with string "Test Type"
    And the column "product_type" from the "demerit" legacy table has been updated with string "Test Type"
    And the column "type" from the "eap" legacy table has been updated with string "Test Type"
    And the column "product_type" from the "meap" legacy table has been updated with string "Test Type"
    And the column "product_type" from the "pip" legacy table has been updated with string "Test Type"
    And the column "type" from the "warranty" legacy table has been updated with string "Test Type"
    And the column "fr" from the "products_categories" legacy table has been updated with string "Test Type"
    And the column "es" from the "products_categories" legacy table has been updated with string "Test Type"
    And the column "zh" from the "products_categories" legacy table has been updated with string "&#27979;&#35797;&#31867;&#22411;"
    And the column "pt" from the "products_categories" legacy table has been updated with string "Test Type"
    And the column "de" from the "products_categories" legacy table has been updated with string "Test Type"
    And the column "ja" from the "products_categories" legacy table has been updated with string "&#12486;&#12473;&#12488;&#12479;&#12452;&#12503;"
    And the column "ru" from the "products_categories" legacy table has been updated with string "&#1058;&#1080;&#1087; &#1090;&#1077;&#1089;&#1090;&#1072;"
    And the column "public" from the "products_categories" legacy table has been updated with integer 1
    And a new row has been inserted in the legacy table "mod_logs"
