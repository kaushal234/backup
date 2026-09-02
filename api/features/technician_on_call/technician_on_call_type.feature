Feature: Test Technician on call Type

  Scenario: Request all TOC types as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_call_types"
    Then the response status code should be 403

  Scenario: Request a single TOC type as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_call_types/1"
    Then the response status code should be 403

  Scenario: Request all TOC types as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_call_types"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call_types.json"
    And the JSON node "hydra:member" should have 4 elements

  Scenario: Request a single TOC type as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_call_types/1"
    Then the response status code should be 200

  Scenario: As basic user, I should not be able to create a TOC type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_call_types" with body:
    """
    {
    }
    """
    Then the response status code should be 405

  Scenario: As a basic user, I should not be able to update a TOC type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_call_types/1" with body:
    """
    {
    }
    """
    Then the response status code should be 405

  Scenario: As a basic user, I should not be able to delete a TOC type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/service/technician_on_call_types/1"
    Then the response status code should be 405