Feature: Test directory division double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create a directory division in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/divisions" with body:
    """
    {
      "name": "onzevision"
    }
    """
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "tld_divisions"

  Scenario: Update a directory division in legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/divisions/1" with body:
    """
    {
      "name": "zero"
    }
    """
    Then the response status code should be 200
    And the column "name" from the "tld_divisions" legacy table has been updated with string "zero"
