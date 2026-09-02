Feature: Test directory region double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create a directory region in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/regions" with the body "tests/fixtures/json/directory_region/dummies/post.json"
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "tld_regions"

  Scenario: Update a directory region in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/regions/1" with the body "tests/fixtures/json/directory_region/dummies/put.json"
    Then the response status code should be 200
    And the column "division" from the "tld_regions" legacy table has been updated
    And the column "repid" from the "tld_regions" legacy table has been updated
    And the column "sub_division_id" from the "tld_regions" legacy table has been updated with integer 3


