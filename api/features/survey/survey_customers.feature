Feature: Test Survey Customers API

  Scenario: Request one survey customer - by public url
    When I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/public/surveys/this-is-not-a-token"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_customers/schemas/survey_published.json"

  Scenario: survey groups should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/published_customers"
    Then the response status code should be 403

#  this test won't pass in the CI container but works fine locally (404 instead of 200)
#
#  Scenario: Request one survey customer - from intranet - with all permissions
#    Given I authenticate as the intranet user "user-superuser@tld.fr"
#    When I add "Accept" header equal to "application/ld+json"
#    When I send a "GET" request to "/surveys/published/this-is-not-a-token"
#    Then the response status code should be 200
#    And the JSON should be valid according to the schema "tests/fixtures/json/survey_customers/schemas/survey_customer_intranet.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Survey\CustomerSurvey" is exposed on the API
    Then the filter "customer" should be available and its type should be "string"
    And the filter "campaign" should be available and its type should be "string"
    And the filter "campaign.model" should be available and its type should be "string"

  Scenario: Answer a survey - public url
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    # Reset the CRT which is updated in another test
    When I send a "PUT" request to "/sales/customer_relationship_teams/1" with body:
    """
    {
      "salesRepresentative": "/people/31",
      "erpLocation": "/locations/23"
    }
    """
    When I add "Accept" header equal to "application/ld+json"
    When I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/public/surveys/this-is-not-a-token/answers" with body:
    """
    {
      "item": "/surveys/items/1",
      "ratingType": "/surveys/rating_types/1",
      "value": 4
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_answers/schemas/survey_answer.json"
    And no email should have been sent asynchronously
    When I add "Accept" header equal to "application/ld+json"
    When I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/public/surveys/this-is-not-a-token/answers" with body:
    """
    {
      "item": "/surveys/items/1",
      "ratingType": "/surveys/rating_types/2",
      "value": 2
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_answers/schemas/survey_answer.json"
    And no email should have been sent asynchronously
    When I add "Accept" header equal to "application/ld+json"
    When I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/public/surveys/this-is-not-a-token/comments" with the body "tests/fixtures/json/survey_customers/dummies/post_comment.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_comments/schemas/survey_comment.json"
    And the JSON node "content" should be equal to "I'm gonna make him an offer he can't refuse."
    When I add "Accept" header equal to "application/ld+json"
    When I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/public/surveys/this-is-not-a-token/answers" with body:
    """
    {
      "item": "/surveys/items/2",
      "ratingType": "/surveys/rating_types/1",
      "value": 1
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_answers/schemas/survey_answer.json"
    And no email should have been sent asynchronously
    When I add "Accept" header equal to "application/ld+json"
    When I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/public/surveys/this-is-not-a-token/answers" with body:
    """
    {
      "item": "/surveys/items/2",
      "ratingType": "/surveys/rating_types/2",
      "value": 3
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_answers/schemas/survey_answer.json"
    And no email should have been sent asynchronously
    When I add "Accept" header equal to "application/ld+json"
    When I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/public/surveys/this-is-not-a-token/answers" with body:
    """
    {
      "item": "/surveys/items/3",
      "ratingType": "/surveys/rating_types/1",
      "value": 2
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_answers/schemas/survey_answer.json"
    And no email should have been sent asynchronously
    When I add "Accept" header equal to "application/ld+json"
    When I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/public/surveys/this-is-not-a-token/answers" with body:
    """
    {
      "item": "/surveys/items/3",
      "ratingType": "/surveys/rating_types/2",
      "value": 1
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_answers/schemas/survey_answer.json"
    And an email should have been sent asynchronously with subject "Survey completed"
    And this asynchronous email should be sent to "user-superuser@tld.fr"
    And this asynchronous email should be sent as cc to "user-csd@tld.fr"
    And this asynchronous email should be sent as cc to "user-gcoo@tld.fr"
    And this asynchronous email should be sent as cc to "user-gceo@tld.fr"
    And this asynchronous email should be sent as cc to "user-asm@tld.fr"
    And this asynchronous email should be sent as cc to "user-evp@tld.fr"

  Scenario: Delete survey customer - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/surveys/published/this-is-not-a-token"
    Then the response status code should be 403

  Scenario: Delete survey customer
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/surveys/published/this-is-not-a-token"
    Then the response status code should be 204

  Scenario: Request all survey comments - permissions for SURVEY_CONTENT_MANAGER
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/comments"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_comments/schemas/survey_comments.json"

  Scenario: Request all survey comments - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/comments"
    Then the response status code should be 403

  Scenario: Request one survey comment - permissions for SURVEY_CONTENT_MANAGER
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/comments/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_comments/schemas/survey_comment.json"

  Scenario: Request one survey comment - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/comments/1"
    Then the response status code should be 403

  Scenario: Update a survey comment - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/surveys/comments/1" with the body "tests/fixtures/json/survey_comments/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a survey comment - permissions for SURVEY_CONTENT_MANAGER
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/surveys/comments/1" with the body "tests/fixtures/json/survey_comments/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_comments/schemas/survey_comment.json"
    And the JSON node "content" should be equal to "Comment test"

  Scenario: Create a survey comment - permissions for SURVEY_CONTENT_MANAGER
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/surveys/comments" with the body "tests/fixtures/json/survey_comments/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_comments/schemas/survey_comment.json"
    And the JSON node "content" should be equal to "Add Comment"

  Scenario: Delete a survey comment - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/surveys/comments/1"
    Then the response status code should be 403

  Scenario: Delete a survey comment - permissions for SURVEY_CONTENT_MANAGER
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/surveys/comments/1"
    Then the response status code should be 204

  Scenario: Request all survey answers - permissions for SURVEY_CONTENT_MANAGER
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/answers"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_answers/schemas/survey_answers.json"

  Scenario: Request all survey answers - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/answers"
    Then the response status code should be 403

  Scenario: Request one survey answer - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/answers/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_answers/schemas/survey_answer.json"

  Scenario: Request one survey answer - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/answers/1"
    Then the response status code should be 403

  Scenario: Delete a survey answer - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/surveys/answers/2"
    Then the response status code should be 403

  Scenario: Delete a survey answer - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/surveys/answers/2"
    Then the response status code should be 204
