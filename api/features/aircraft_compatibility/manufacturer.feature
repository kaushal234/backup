Feature: Manufacturers can be read using the API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Sales\AircraftCompatibility\Manufacturer" should only be available for intranet user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\AircraftCompatibility\Manufacturer" is exposed on the API
    And the filter "order[name]" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"

  Scenario: User basic should be able to list manufacturers
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/manufacturers"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/manufacturer/schemas/manufacturers.json"

  Scenario: User basic should be able to fetch an aircraft
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/manufacturers/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/manufacturer/schemas/manufacturer.json"

  Scenario: User basic should not be allowed to create a manufacturer
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/sales/manufacturers" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: User basic should not be allowed to edit an manufacturer
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/sales/manufacturers/1" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: User basic should not be able to delete an aircraft
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/sales/manufacturers/1"
    Then the response status code should be 405