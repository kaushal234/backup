Feature: Aircrafts can be created and edited using the API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Sales\AircraftCompatibility\Aircraft" should only be available for intranet user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\AircraftCompatibility\Aircraft" is exposed on the API
    And the filter "order[name]" should be available and its type should be "string"
    And the filter "order[manufacturer.name]" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"

  Scenario: User basic should be able to list aircrafts
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/aircrafts"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/aircraft/schemas/aircrafts.json"

  Scenario: User basic should be able to fetch an aircraft
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/aircrafts/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/aircraft/schemas/aircraft.json"

  Scenario: User basic should not be allowed to create an aircraft
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/sales/aircrafts" with body:
    """
    {
      "name": "B787",
      "manufacturer": "/sales/manufacturers/1"
    }
    """
    Then the response status code should be 403

  Scenario: PSE should be allowed to create an aircraft
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/sales/aircrafts" with body:
    """
    {
      "name": "B787",
      "manufacturer": "/sales/manufacturers/1"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/aircraft/schemas/aircraft.json"
    And the JSON node "name" should be equal to the string "B787"
    And the JSON node "manufacturer.name" should be equal to the string "BOEING"

  Scenario: PSE should not be allowed to create an aircraft with an already existing name
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/sales/aircrafts" with body:
    """
    {
      "name": "B787",
      "manufacturer": "/sales/manufacturers/1"
    }
    """
    Then the response status code should be 422

  Scenario: PSE should not be allowed to create an aircraft with an empty name
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/sales/aircrafts" with body:
    """
    {
      "name": "",
      "manufacturer": "/sales/manufacturers/1"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "This value should not be blank."

  Scenario: User basic should not be allowed to update an aircraft
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/sales/aircrafts/3" with body:
    """
    {
      "name": "A330",
      "manufacturer": "/sales/manufacturers/2"
    }
    """
    Then the response status code should be 403

  Scenario: PSE should be allowed to edit an aircraft
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/sales/aircrafts/3" with body:
    """
    {
      "name": "A330",
      "manufacturer": "/sales/manufacturers/2"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/aircraft/schemas/aircraft.json"

    And the JSON node "name" should be equal to the string "A330"
    And the JSON node "manufacturer.name" should be equal to the string "AIRBUS"

  Scenario: User basic should not be able to delete an aircraft
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/sales/aircrafts/3"
    Then the response status code should be 403

  Scenario: PSE should not be able to delete a used aircraft
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/sales/aircrafts/1"
    Then the response status code should be 422
    And the JSON node "detail" should be equal to the string "The Aircraft 'A380' is not deletable because it is used by 3 Aircraft Compatibility/ies (1, 2, 3)"

  Scenario: PSE should be able to delete an aircraft
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/sales/aircrafts/3"
    Then the response status code should be 204
