Feature: Test Unit Operational Status API

  Scenario: Request all Unit Operational Statuses without being authenticated
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/unit_operational_statuses"
    Then the response status code should be 401

  Scenario: Request all Unit Operational Statuses
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/unit_operational_statuses"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/unit_operational_status/schemas/unit_operational_statuses.json"
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/unit_operational_statuses"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/unit_operational_status/schemas/unit_operational_statuses.json"

  Scenario: Request a single Unit Operational Statuses
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/unit_operational_statuses/MCF"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/unit_operational_status/schemas/unit_operational_status.json"
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/unit_operational_statuses/MCF"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/unit_operational_status/schemas/unit_operational_status.json"

  Scenario: Update an Unit Operational Statuses should not be allowed (handled only by migrations)
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/unit_operational_statuses/MCF"
    Then the response status code should be 405

  Scenario: Create an Unit Operational Statuses should not be allowed (handled only by migrations)
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/unit_operational_statuses"
    Then the response status code should be 405
