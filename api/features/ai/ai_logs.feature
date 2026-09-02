Feature: Test endpoints of AI Logs API
  Scenario: Get AI Logs without being authenticated should not be allowed
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai_logs"
    Then the response status code should be 401

  Scenario: Filters are declared on resource
    Given the class "App\Entity\AI\AILog" is exposed on the API
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "order[pinned]" should be available and its type should be "string"
    And the filter "type" should be available and its type should be "string"
    And the filter "people" should be available and its type should be "string"

  Scenario: AI Logs should be accessible only for intranet users
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai_logs"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai_logs"
    Then the response status code should be 403

  Scenario: AI Logs should be accessible for all users, but they should see only their logs
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai_logs"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/ai_logs/schemas/ai_logs.json"
    And the JSON node "hydra:member" should have 1 element
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai_logs/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/ai_logs/schemas/ai_log.json"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai_logs"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/ai_logs/schemas/ai_logs.json"
    And the JSON node "hydra:member" should have 1 element
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai_logs/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/ai_logs/schemas/ai_log.json"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai_logs/1"
    Then the response status code should be 404

  Scenario: Create an AI Log should be possible for basic users
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ai_logs" with body:
    """
    {
      "type": "chat"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/ai_logs/schemas/ai_log.json"
    And the JSON node "createdAt" should be newer than 1 minute ago

  Scenario: Delete an AI Log should be possible for the owner of the AI Log
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/ai_logs/2"
    Then the response status code should be 404
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/ai_logs/3"
    Then the response status code should be 204

  Scenario: AI Logs history endpoint should be accessible only for user-cio or user-mism
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai_logs/history"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-cio@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai_logs/history"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/ai_logs/schemas/ai_logs_history.json"
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai_logs/history"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/ai_logs/schemas/ai_logs_history.json"

  Scenario: Update an ai log should be authorized only for pinned property and for owner of log
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/ai_logs/1" with body:
    """
    {
      "pinned": true,
    }
    """
    Then the response status code should be 404
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/ai_logs/1" with body:
    """
    {
      "pinned": true,
      "title": "test"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/ai_logs/schemas/ai_log.json"
    And the JSON node "pinned" should be true
    And the JSON node "title" should be null