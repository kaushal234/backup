Feature: Test endpoints of AI Rating API
  Scenario: Get ratings without being authenticated should not be allowed
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/ratings"
    Then the response status code should be 401

  Scenario: AI Ratings should be accessible only for intranet users
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/ratings"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/ratings"
    Then the response status code should be 403

  Scenario: User can post only a rating on his own logs, and only one rating per log
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ai/ratings" with body:
    """
    {
      "rating": 4,
      "comment": "test",
      "log": "/ai_logs/1"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should contain "Item not found"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ai/ratings" with body:
    """
    {
      "rating": 5,
      "comment": "test",
      "log": "/ai_logs/2"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "This value should be between 1 and 4."
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ai/ratings" with body:
    """
    {
      "rating": 4,
      "comment": "test",
      "log": "/ai_logs/2"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/rating/schemas/rating.json"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ai/ratings" with body:
    """
    {
      "rating": 2,
      "comment": "test",
      "log": "/ai_logs/1"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/rating/schemas/rating.json"

  Scenario: AI Ratings should be accessible for all users
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/ratings"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/rating/schemas/ratings.json"
    And the JSON node "hydra:member" should have 2 elements
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/ratings/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/rating/schemas/rating.json"

  Scenario: Update or delete an ai rating should not be authorized
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/ai/ratings/1" with body:
    """
    {}
    """
    Then the response status code should be 405
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/ai/ratings/1" with body:
    """
    {}
    """
    Then the response status code should be 405