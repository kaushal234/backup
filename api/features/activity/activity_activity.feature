Feature: Test Activity Comment API

  Scenario: resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Activity\Activity" should only be available for intranet user

  Scenario: Request all activities
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/activities"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/activity_activity/schemas/activities.json"

  Scenario: Request a single activity
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/activities/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/activity_activity/schemas/activity.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Activity\Activity" is exposed on the API
    Then the filter "resource" should be available and its type should be "string"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "user" should be available and its type should be "string"
