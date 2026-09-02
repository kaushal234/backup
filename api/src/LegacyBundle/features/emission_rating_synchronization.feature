Feature: Test emission ratings double write API

  Scenario: Create an emission rating in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/emission_ratings" with the body "tests/fixtures/json/emission_rating/dummies/post.json"
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "lists"
    And the column "list_name" from the "lists" legacy table has been inserted with string "list.engine.tiers"
    And the column "list_item" from the "lists" legacy table has been inserted with string matching "Tier evenu \?"

  Scenario: Update a sales forecast in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/emission_ratings/1" with the body "tests/fixtures/json/emission_rating/dummies/put.json"
    Then the response status code should be 200
    And the column "list_name" from the "lists" legacy table has been updated with string "list.engine.tiers_obsolete"
