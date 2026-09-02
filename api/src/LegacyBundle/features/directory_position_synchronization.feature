Feature: Test position double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create a directory position in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/positions" with the body "tests/fixtures/json/directory_position/dummies/post.json"
    Then the response status code should be 201
    Then a new row has been inserted in the legacy table "tld_functions"

  Scenario: Update a directory position in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/positions/1" with the body "tests/fixtures/json/directory_position/dummies/put.json"
    Then the response status code should be 200
    And the column "code" from the "tld_functions" legacy table has been updated
    And the column "dsc" from the "tld_functions" legacy table has been updated
    And the column "level" from the "tld_functions" legacy table has been updated with string "MANAGERS"
