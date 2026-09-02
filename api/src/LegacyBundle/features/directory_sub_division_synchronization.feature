Feature: Test directory subdivision double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create a directory subdivision in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sub_divisions" with body:
    """
    {
      "name": "sub",
      "division": "/divisions/1"
    }
    """
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "tld_sub_divisions"

  Scenario: Update a directory subdivision in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sub_divisions/2" with body:
    """
    {
      "name": "seub",
      "division": "/divisions/2"
    }
    """
    Then the response status code should be 200
    And the column "name" from the "tld_sub_divisions" legacy table has been updated with string "seub"
    And the column "division_id" from the "tld_sub_divisions" legacy table has been updated with integer 2


