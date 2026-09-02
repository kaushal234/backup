Feature: Test notification templates

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Common\Notification\NotificationTemplate" should only be available for intranet user

  Scenario: Request all notification templates should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/notification_templates"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification_template/schemas/notification_templates.json"

  Scenario: Request one notification templates should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/notification_templates/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/notification_template/schemas/notification_template.json"

  Scenario: It should not be possible to create, put or delete notification
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/notification_templates"
    Then the response status code should be 405
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "PUT" request to "/notification_templates/1"
    Then the response status code should be 405
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/notification_templates/1"
    Then the response status code should be 405