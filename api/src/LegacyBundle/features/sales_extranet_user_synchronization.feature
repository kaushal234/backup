Feature: Test extranet users double write API

  Scenario: Create an extranet user in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_users" with the body "tests/fixtures/json/sales/extranet_user/dummies/post.json"
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "extranet_users"
    And 2 new rows have been inserted in the legacy table "mod_logs"

  Scenario: Update an extranet user in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/205" with the body "tests/fixtures/json/sales/extranet_user/dummies/put.json"
    Then the response status code should be 200
    And the column "type" from the "extranet_users" legacy table has been updated with string "PUNCHOUT"
    And the column "salutation" from the "extranet_users" legacy table has been updated with string "Mr"
    And the column "division" from the "extranet_users" legacy table has been updated with string "division test"
    And the column "department" from the "extranet_users" legacy table has been updated with string "department test"
    And the column "title" from the "extranet_users" legacy table has been updated with string "Eleveur de chèvres"
    And the column "lang" from the "extranet_users" legacy table has been updated with string "FR"
    And the column "phone" from the "extranet_users" legacy table has been updated with string "+33 5 17 17 17 18"
    And the column "direct_phone" from the "extranet_users" legacy table has been updated with string "+33 5 40 40 40 41"
    And the column "mobile" from the "extranet_users" legacy table has been updated with string "+33 6 69 69 69 68"
    And the column "fax" from the "extranet_users" legacy table has been updated with string "+33 2 00 01 10 01"
    And the column "home_phone" from the "extranet_users" legacy table has been updated with string "+33 01 01 01 01 02"
    And the column "address" from the "extranet_users" legacy table has been updated with a string containing "allée du peuplier\nSmallville\nCP 37700 Metropolis\nCA"
    And the column "shipping_address" from the "extranet_users" legacy table has been updated with a string containing "rue Flaquette\nBarb Ship\nCP 17700 Zen\nCA\nItaly"
    And the column "country" from the "extranet_users" legacy table has been updated with string "Kinder"
    And the column "customer_name" from the "extranet_users" legacy table has been updated with string "ASM WILL BE FIRED"
    And the column "cust_carrier_name" from the "extranet_users" legacy table has been updated with string "FEDEX"
    And the column "company_name" from the "extranet_users" legacy table has been updated with string "Custo_Morti_Mer_Rouge"
    And the column "note" from the "extranet_users" legacy table has been updated with string "I love you"
    And the column "ship_acct_num" from the "extranet_users" legacy table has been updated with string "123"
    And the column "erp" from the "extranet_users" legacy table has been updated with integer 72
    And the column "requestor_num" from the "extranet_users" legacy table has been updated with string "456"
    And the column "employe_num" from the "extranet_users" legacy table has been updated with string "789"
    And 2 new rows have been inserted in the legacy table "mod_logs"

  Scenario: Update an extranet user in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/254" with the body "tests/fixtures/json/sales/extranet_user/dummies/put_chinese.json"
    Then the response status code should be 200
    And the column "lastname" from the "extranet_users" legacy table has been updated with string "&#24179;"
    And the column "firstname" from the "extranet_users" legacy table has been updated with string "&#20050;&#20051;"
    And a new row has been inserted in the legacy table "mod_logs"

  Scenario: Update an extranet user in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_users/255" with the body "tests/fixtures/json/sales/extranet_user/dummies/put_double_write.json"
    Then the response status code should be 200
    And the column "email" from the "extranet_users" legacy table has been updated with string "tagada@tld.fr"
    And the column "userid" from the "extranet_users" legacy table has been updated with string "tagada@tld.fr"
    And the column "enable" from the "extranet_users" legacy table has been updated with string "N"
    And the column "hidden" from the "extranet_users" legacy table has been updated with integer 1

  Scenario: Create an extranet user in legacy database with TLD people email
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_users" with the body "tests/fixtures/json/sales/extranet_user/dummies/post_with_existing_people_email.json"
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "extranet_users"
    And the column "userid" from the "extranet_users" legacy table has been inserted with string "user-basic@tld.fr"
    And the column "email" from the "extranet_users" legacy table has been inserted with string "user-basic@tld.fr"
