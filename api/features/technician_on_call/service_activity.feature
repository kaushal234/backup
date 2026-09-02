Feature: Test Service Activity

  Scenario: Request all Service Activities as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/service_activities"
    Then the response status code should be 403

  Scenario: Request a single Service Activity as authorized application
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/service_activities/1"
    Then the response status code should be 403

  Scenario: Request all Service Activities as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/service_activities"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/service_activities.json"
    And the JSON node "hydra:member" should have 7 elements

  Scenario: Request a single Service Activity as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/service_activities/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/service_activity.json"

  Scenario: As an extranet user, the Commissioning activity should be hidden
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/service_activities"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 6 elements

  Scenario: As an extranet user, the Commissioning activity should not be reachable
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/service_activities/2"
    Then the response status code should be 404

  Scenario: As basic user, I should not be able to create a Service Activity
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/service_activities" with body:
    """
    {
    }
    """
    Then the response status code should be 405

  Scenario: As a basic user, I should not be able to update a Service Activity
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/service_activities/1" with body:
    """
    {
    }
    """
    Then the response status code should be 405

  Scenario: As a basic user, I should not be able to delete a Service Activity
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/service/service_activities/1"
    Then the response status code should be 405