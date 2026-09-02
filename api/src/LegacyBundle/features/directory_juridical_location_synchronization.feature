Feature: Test juridical location double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create a juridical location in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/juridical_locations" with the body "tests/fixtures/json/directory_juridical_location/dummies/post.json"
    Then the response status code should be 201
    Then a new row has been inserted in the legacy table "tld_juridical_locations"

  Scenario: Update a juridical location in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/juridical_locations/1" with the body "tests/fixtures/json/directory_juridical_location/dummies/put.json"
    Then the response status code should be 200
    And the column "name" from the "tld_juridical_locations" legacy table has been updated
    And the column "address" from the "tld_juridical_locations" legacy table has been updated with a string containing "rue Flaquette\nBarb Ship\nCP 17700 Zen\nCA\nItaly"

