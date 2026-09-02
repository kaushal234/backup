Feature: Test acronym double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create an acronym in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/acronyms" with the body "tests/fixtures/json/acronym/dummies/post.json"
    Then the response status code should be 201
    Then a new row has been inserted in the legacy table "agr"

  Scenario: Update an acronym in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/acronyms/1" with the body "tests/fixtures/json/acronym/dummies/put.json"
    Then the response status code should be 200
    And the column "acronym" from the "agr" legacy table has been updated
    And the column "descb" from the "agr" legacy table has been updated with string ""
    And the column "desca" from the "agr" legacy table has been updated
    And the column "url" from the "agr" legacy table has been updated with string ""
    Then a row where "parent_id" with value 99 has been deleted from "agrl" legacy table
    Then 2 new rows have been inserted in the legacy table "agrl"
