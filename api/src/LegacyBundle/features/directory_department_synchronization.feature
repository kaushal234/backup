Feature: Test directory department double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create a directory department in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/departments" with the body "tests/fixtures/json/directory_department/dummies/post.json"
    Then the response status code should be 201
    Then a new row has been inserted in the legacy table "tld_departments"

  Scenario: Update a directory department in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/departments/1" with the body "tests/fixtures/json/directory_department/dummies/put.json"
    Then the response status code should be 200
    And the column "dpt" from the "tld_departments" legacy table has been updated
    And the column "sso" from the "tld_departments" legacy table has been updated with string "N"
    And the column "erp" from the "tld_departments" legacy table has been updated with string "Y"

