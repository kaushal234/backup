Feature: Test Survey Rating Types Api

    Scenario: Resource should only be accessible for intranet users
        Given I add "Accept" header equal to "application/ld+json"
        Then the resource "App\Entity\Survey\RatingType" should only be available for intranet user

    Scenario: Request all survey rating types - permissions OK
        Given I authenticate as the intranet user "user-superuser@tld.fr"
        When I add "Accept" header equal to "application/ld+json"
        When I send a "GET" request to "/surveys/rating_types"
        Then the response status code should be 200
        And the JSON should be valid according to the schema "tests/fixtures/json/survey_rating_types/schemas/survey_rating_types.json"

    Scenario: Filters are declared on resource
        Given the class "App\Entity\Survey\RatingType" is exposed on the API
        Then the filter "description" should be available and its type should be "string"

    Scenario: Request one survey rating type - permissions OK
        Given I authenticate as the intranet user "user-superuser@tld.fr"
        When I add "Accept" header equal to "application/ld+json"
        When I send a "GET" request to "/surveys/rating_types/1"
        Then the response status code should be 200
        And the JSON should be valid according to the schema "tests/fixtures/json/survey_rating_types/schemas/survey_rating_type.json"

    Scenario: Update a survey rating type - insufficient permissions
        Given I authenticate as the intranet user "user-basic@tld.fr"
        When I add "Accept" header equal to "application/ld+json"
        And I add "Content-type" header equal to "application/ld+json"
        When I send a "PUT" request to "/surveys/rating_types/1" with the body "tests/fixtures/json/survey_rating_types/dummies/put.json"
        Then the response status code should be 403

    Scenario: Update a survey rating type - permissions OK
        Given I authenticate as the intranet user "user-superuser@tld.fr"
        When I add "Accept" header equal to "application/ld+json"
        And I add "Content-type" header equal to "application/ld+json"
        When I send a "PUT" request to "/surveys/rating_types/1" with the body "tests/fixtures/json/survey_rating_types/dummies/put.json"
        Then the response status code should be 200
        And the JSON should be valid according to the schema "tests/fixtures/json/survey_rating_types/schemas/survey_rating_type.json"
        And the JSON node "min" should be equal to 1
        And the JSON node "max" should be equal to 4

    Scenario: Create a survey rating type - permissions OK
        Given I authenticate as the intranet user "user-superuser@tld.fr"
        When I add "Accept" header equal to "application/ld+json"
        And I add "Content-type" header equal to "application/ld+json"
        When I send a "POST" request to "/surveys/rating_types" with the body "tests/fixtures/json/survey_rating_types/dummies/post.json"
        Then the response status code should be 201
        And the JSON should be valid according to the schema "tests/fixtures/json/survey_rating_types/schemas/survey_rating_type.json"
        And the JSON node "min" should be equal to 1
        And the JSON node "max" should be equal to 4
        And the JSON node "minLabel" should be equal to "min label"
        And the JSON node "maxLabel" should be equal to "max label"

    Scenario: Delete a survey rating type - insufficient permissions
        Given I authenticate as the intranet user "user-basic@tld.fr"
        When I add "Accept" header equal to "application/ld+json"
        And I add "Content-type" header equal to "application/ld+json"
        When I send a "DELETE" request to "/surveys/rating_types/3"
        Then the response status code should be 403

    Scenario: Delete a survey rating type - permissions OK
        Given I authenticate as the intranet user "user-superuser@tld.fr"
        When I add "Accept" header equal to "application/ld+json"
        And I add "Content-type" header equal to "application/ld+json"
        When I send a "DELETE" request to "/surveys/rating_types/3"
        Then the response status code should be 204
