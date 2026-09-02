Feature: Test acl double write API

  Scenario: Create a comment
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with the body "tests/fixtures/json/activity_comment/dummies/post.json"
    Then the response status code should be 201
    And 2 new rows have been inserted in the legacy table "mod_logs"

  Scenario: Create a log
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/acronyms/1" with body:
    """
    {
      "acronym": "DTC"
    }
    """
    Then the response status code should be 200
    And a new row has been inserted in the legacy table "mod_logs"
