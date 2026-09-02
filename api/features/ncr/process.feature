Feature: Test Process Entity

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Quality\Process" should only be available for intranet user

  Scenario: Request all processes as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/processes"
    Then the response status code should be 403

  Scenario: Request a single process as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/processes/1"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Quality\Process" is exposed on the API
    Then the filter "order[category]" should be available and its type should be "string"

  Scenario: Request all processes as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/processes"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/process/schemas/processes.json"

  Scenario: Request a single process as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/processes/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/process/schemas/process.json"


  Scenario: Update a process should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/processes/1" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: Create a process should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/processes" with body:
    """
    {}
    """
    Then the response status code should be 405
