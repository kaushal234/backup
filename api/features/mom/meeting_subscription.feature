Feature: Meetings can have subscribers

  Scenario: Create a subscription on a meeting requires to be granted EDIT permissions on the given meeting
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/subscriptions" with body:
    """
    {
      "resource": "/minutes_of_meeting/meetings/2",
      "user": "/people/11"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/subscriptions" with body:
    """
    {
      "resource": "/minutes_of_meeting/meetings/2",
      "user": "/people/11"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/subscription/schemas/subscription.json"
    And no email should have been sent asynchronously

  Scenario: Delete a subscription on a meeting requires to be granted EDIT permissions on the given meeting
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/subscriptions/5"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/subscriptions/5"
    Then the response status code should be 204
