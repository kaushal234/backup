Feature: Test endpoints of AI File API

  Scenario: Get an AI File without being authenticated should not be allowed
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai_files/13"
    Then the response status code should be 401

  Scenario: Get a non-existent AI File should return 404
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai_files/9999"
    Then the response status code should be 404

  Scenario: Get an AI File as an authenticated intranet user should be allowed
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai_files/13"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: Create, update or delete an AI File should not be authorized
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ai_files" with body:
    """
    {}
    """
    Then the response status code should be 404
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/ai_files/13" with body:
    """
    {}
    """
    Then the response status code should be 405
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/ai_files/13"
    Then the response status code should be 405

  Scenario: Download an AI log attached file as unauthorized user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai_files/13/download"
    Then the response status code should be 403

  Scenario: Download an AI log attached file as authorized user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai_files/13/download"
    Then the response status code should be 200
