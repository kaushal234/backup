Feature: Test user group double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create a user group in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/groups" with the body "tests/fixtures/json/mis_group/dummies/post.json"
    Then the response status code should be 201
    Then a new row has been inserted in the legacy table "people_groups_select"

  Scenario: Update a user group in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/groups/2" with the body "tests/fixtures/json/mis_group/dummies/put.json"
    Then the response status code should be 200
    And the column "group_name" from the "people_groups_select" legacy table has been updated with string "ETTE"
    And the column "group_name" from the "people_groups" legacy table has been updated with string "ETTE"
    And the column "group_name" from the "cal_seq_tpl_nodes" legacy table has been updated with string "ETTE"
    And the column "description" from the "people_groups_select" legacy table has been updated with string "gROUP ETTE description"
