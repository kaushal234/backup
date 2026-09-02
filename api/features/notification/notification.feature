Feature: Test notification

  Scenario: Request all notifications should not be possible for vendor users or extranet users
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 403

  Scenario: Request all notifications should only display mine
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notifications.json"
    And the JSON node "hydra:totalItems" should be equal to 3
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 0

  Scenario: I should only be able to access my notification (item route)
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notification.json"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications/1"
    Then the response status code should be 403

  Scenario: It should not be possible to create notification
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/notifications"
    Then the response status code should be 405

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Common\Notification\Notification" is exposed on the API
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "template.module.name" should be available and its type should be "string"
    And the filter "unread" should be available and its type should be "bool"
    And the filter "q" should be available and its type should be "string"

  Scenario: Only me can edit my notification, and only unread property
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/notifications/1"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/notifications/1" with body:
      """
      {
        "unread": false,
        "textDisplayed": "toto"
      }
      """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification/schemas/notification.json"
    And the JSON node "unread" should be false
    And the JSON node "textDisplayed" should be equal to the string "TTS#1: This TTS is assigned to you and required action on your side."

  Scenario: Only me can remove my notifications
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/notifications/1"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/notifications/1"
    Then the response status code should be 204

  Scenario: Only me can remove all my notifications
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/notifications/delete_all"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/notifications"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 0
