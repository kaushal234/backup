Feature: Test location areas API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Quality\LocationArea" should only be available for intranet user

  Scenario: Request all location areas
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/location_areas"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/location_area/schemas/location_areas.json"

  Scenario: Request a single location area
    And I add "Accept" header equal to "application/ld+json"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I send a "GET" request to "/location_areas/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/location_area/schemas/location_area.json"

  Scenario: Update a given location area - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/location_areas/1" with the body "tests/fixtures/json/location_area/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a given location area - permissions OK
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/location_areas/1" with the body "tests/fixtures/json/location_area/dummies/put.json"
    Then the response status code should be 200
    And the JSON node "name" should be equal to "iasi"

  Scenario: Create a location area - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/location_areas" with the body "tests/fixtures/json/location_area/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create a calibration log - permissions OK
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/location_areas" with the body "tests/fixtures/json/location_area/dummies/post.json"
    Then the response status code should be 201
    And the JSON nodes should be equal to:
      | name | iasi |
      | factory.@id | /locations/1 |
      | supervisor.@id | /people/12 |

  Scenario: Delete a location area - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/location_areas/3"
    Then the response status code should be 403

  Scenario: Delete a location area - permissions OK
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/location_areas/4"
    Then the response status code should be 204

  Scenario: Delete a used location area
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/location_areas/1"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The location area 'iasi' is not deletable because it is used by 7 Tools (1, 18, 19, 20, 21, 22, 23)"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Quality\LocationArea" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"
    And the filter "name" should be available and its type should be "string"
    And the filter "factory" should be available and its type should be "string"
    And the filter "supervisor" should be available and its type should be "string"
