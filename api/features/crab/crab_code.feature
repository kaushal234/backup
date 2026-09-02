Feature: Test CrabCode Entity

  Scenario: Request all crabs codes
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/crab_codes"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/crab_code/schemas/crab_codes.json"

  Scenario: Request a single crab code
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/crab_codes/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/crab_code/schemas/crab_code.json"

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Quality\CrabCode" should only be available for "intranet" user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Quality\CrabCode" is exposed on the API
    Then the filter "order[code]" should be available and its type should be "string"

  Scenario: Create a crab code should not be possible
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/crab_codes" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: Update a crab code should not be possible
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crab_codes/1" with body:
    """
    {}
    """
    Then the response status code should be 405
