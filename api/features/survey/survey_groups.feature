Feature: Test survey item groups Api

    Scenario: Resource should only be accessible for intranet users
        Given I add "Accept" header equal to "application/ld+json"
        Then the resource "App\Entity\Survey\Group" should only be available for intranet user

    Scenario: Request all survey item groups - permissions OK
        Given I authenticate as the intranet user "user-superuser@tld.fr"
        When I add "Accept" header equal to "application/ld+json"
        When I send a "GET" request to "/surveys/groups"
        Then the response status code should be 200
        And the JSON should be valid according to the schema "tests/fixtures/json/survey_item_groups/schemas/survey_item_groups.json"

    Scenario: Filters are declared on resource
        Given the class "App\Entity\Survey\Group" is exposed on the API
        Then the filter "name" should be available and its type should be "string"
        And the filter "survey" should be available and its type should be "string"
        And the filter "createdBy" should be available and its type should be "string"

    Scenario: Request one survey item group - permissions OK
        Given I authenticate as the intranet user "user-superuser@tld.fr"
        When I add "Accept" header equal to "application/ld+json"
        When I send a "GET" request to "/surveys/groups/1"
        Then the response status code should be 200
        And the JSON should be valid according to the schema "tests/fixtures/json/survey_item_groups/schemas/survey_item_group.json"

    Scenario: Update a survey item group - insufficient permissions
        Given I authenticate as the intranet user "user-basic@tld.fr"
        When I add "Accept" header equal to "application/ld+json"
        And I add "Content-type" header equal to "application/ld+json"
        When I send a "PUT" request to "/surveys/groups/1" with the body "tests/fixtures/json/survey_item_groups/dummies/put.json"
        Then the response status code should be 403

    Scenario: Update a survey item group - permissions OK
        Given I authenticate as the intranet user "user-superuser@tld.fr"
        When I add "Accept" header equal to "application/ld+json"
        And I add "Content-type" header equal to "application/ld+json"
        When I send a "PUT" request to "/surveys/groups/1" with the body "tests/fixtures/json/survey_item_groups/dummies/put.json"
        Then the response status code should be 200
        And the JSON should be valid according to the schema "tests/fixtures/json/survey_item_groups/schemas/survey_item_group.json"
        And the JSON node "name" should be equal to "update Group"
        And the JSON node "description" should be equal to "Scenario has changed this."

    Scenario: Create a survey item group - permissions OK
        Given I authenticate as the intranet user "user-superuser@tld.fr"
        When I add "Accept" header equal to "application/ld+json"
        And I add "Content-type" header equal to "application/ld+json"
        When I send a "POST" request to "/surveys/groups" with the body "tests/fixtures/json/survey_item_groups/dummies/post.json"
        Then the response status code should be 201
        And the JSON should be valid according to the schema "tests/fixtures/json/survey_item_groups/schemas/survey_item_group.json"
        And the JSON node "name" should be equal to "Posting Group"
        And the JSON node "description" should be equal to "Posting Group description"
        And the JSON node "sorting" should be equal to 50
        And the JSON node "survey.@id" should be equal to "/surveys/models/2"

    Scenario: Delete a survey item group - insufficient permissions
        Given I authenticate as the intranet user "user-basic@tld.fr"
        When I add "Accept" header equal to "application/ld+json"
        And I add "Content-type" header equal to "application/ld+json"
        When I send a "DELETE" request to "/surveys/groups/2"
        Then the response status code should be 403

    Scenario: Delete a survey item group - permissions OK
        Given I authenticate as the intranet user "user-superuser@tld.fr"
        When I add "Accept" header equal to "application/ld+json"
        And I add "Content-type" header equal to "application/ld+json"
        When I send a "DELETE" request to "/surveys/groups/7"
        Then the response status code should be 204
