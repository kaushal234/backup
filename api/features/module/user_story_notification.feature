Feature: Test notification of user_story

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Module\Specification\Notification" should only be available for intranet user

  Scenario: I can Get one notification
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/user_story_notifications/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_story_notifications/schemas/notification.json"

  Scenario: I can Get all notifications
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/user_story_notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_story_notifications/schemas/notifications.json"

  Scenario: I can't POST a notification
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/user_story_notifications" with body:
    """
    {
    }
    """
    Then the response status code should be 405

  Scenario:  I can't Update a notification
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/user_story_notifications/20" with body:
    """
    {
    }
    """
    Then the response status code should be 405

  Scenario: I can't delete a notification
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/user_story_notifications/11"
    Then the response status code should be 405