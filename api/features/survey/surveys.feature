Feature: Test Surveys API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Survey\Survey" should only be available for intranet user

  Scenario: Request all surveys as superuser - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/models"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/surveys/schemas/surveys.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Survey\Survey" is exposed on the API
    Then the filter "name" should be available and its type should be "string"
    And the filter "createdBy" should be available and its type should be "string"
    And the filter "description" should be available and its type should be "string"

  Scenario: Request a single survey - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/models/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/surveys/schemas/survey.json"

  Scenario: Update a given survey - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/surveys/models/1" with the body "tests/fixtures/json/surveys/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a given survey - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/surveys/models/1" with the body "tests/fixtures/json/surveys/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/surveys/schemas/survey.json"
    And the JSON node "name" should be equal to "testing"
    And the JSON node "description" should be equal to "1234567"

  Scenario: Add a survey - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/surveys/models" with the body "tests/fixtures/json/surveys/dummies/post.json"
    Then the response status code should be 403

  Scenario: Add a survey - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/surveys/models" with the body "tests/fixtures/json/surveys/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/surveys/schemas/survey.json"
    And the JSON node "name" should be equal to "testing post"
    And the JSON node "description" should be equal to "testing post"

  Scenario: Delete a survey - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/surveys/models/4"
    Then the response status code should be 403

  Scenario: Delete a survey - permissions OK
    Given I authenticate as the intranet user "user-gcoo@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/surveys/models/3"
    Then the response status code should be 204
