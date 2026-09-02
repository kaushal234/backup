Feature: Test Activity Log API

  Scenario: Request all logs without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims"
    Then the response status code should be 401

  Scenario: Request a log without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_warranty_claims/1"
    Then the response status code should be 401

  Scenario: Request all logs as extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/logs"
    Then the response status code should be 403

  Scenario: Request a single log as extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/logs/15"
    Then the response status code should be 403

  Scenario: Request all logs
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/logs"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/activity_log/schemas/logs.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Activity\Log" is exposed on the API
    Then the filter "resource" should be available and its type should be "string"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"

  Scenario: Request a given log
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/logs/15"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/activity_log/schemas/log.json"


