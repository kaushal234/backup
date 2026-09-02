Feature: Test Survey Items Api

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Survey\Item" should only be available for intranet user

  Scenario: Request all survey items - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/items"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_items/schemas/survey_items.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Survey\Item" is exposed on the API
    Then the filter "description" should be available and its type should be "string"
    And the filter "group" should be available and its type should be "string"
    And the filter "survey" should be available and its type should be "string"
    And the filter "createdBy" should be available and its type should be "string"
    And the filter "updatedBy" should be available and its type should be "string"

  Scenario: Request one survey item
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/items/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_items/schemas/survey_item.json"

  Scenario: Update a survey item - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/surveys/items/1" with the body "tests/fixtures/json/survey_items/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a survey item - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/surveys/items/1" with the body "tests/fixtures/json/survey_items/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_items/schemas/survey_item.json"
    And the JSON node "description" should be equal to "how does behat even work?"

  Scenario: Create a survey item - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/surveys/items" with the body "tests/fixtures/json/survey_items/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_items/schemas/survey_item.json"
    And the JSON node "description" should be equal to "how does behat even work?"

  Scenario: Delete a survey item - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/surveys/items/2"
    Then the response status code should be 403

  Scenario: Delete a survey item - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/surveys/items/3"
    Then the response status code should be 204

  Scenario: Request all survey items - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/items"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_items/schemas/survey_items.json"
