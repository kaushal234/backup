Feature: Test notification_access of module

  Scenario: Resource should not be available for extranet users
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/user_story_notifications/1"
    Then the response status code should be 403


  Scenario: Resource should not be available for evendors users
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/user_story_notifications/1"
    Then the response status code should be 403

  Scenario: I can Get one notification access
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/notification_accesses/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_story_notifications_accesses/schemas/notifications_access.json"

  Scenario: I can Get all notification access
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/notification_accesses"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_story_notifications_accesses/schemas/notifications_accesses.json"

  Scenario: I can't Post a notification access
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/notification_accesses" with body:
    """
    {
    }
    """
    Then the response status code should be 405

  Scenario: I can't Put(update) a notification access
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/notification_accesses/1" with body:
    """
    {
    }
    """
    Then the response status code should be 405

  Scenario: I can't delete a notification access
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/notification_accesses/1"
    Then the response status code should be 405