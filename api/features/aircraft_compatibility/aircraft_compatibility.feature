Feature: Aircraft Compatibilities can be created and edited using the API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Sales\AircraftCompatibility\AircraftCompatibility" should only be available for intranet user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\AircraftCompatibility\AircraftCompatibility" is exposed on the API
    And the filter "order[aircrafts.name]" should be available and its type should be "string"
    And the filter "order[products.name]" should be available and its type should be "string"
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "products" should be available and its type should be "string"
    And the filter "products.family" should be available and its type should be "string"
    And the filter "products.family.productType" should be available and its type should be "string"
    And the filter "aircrafts" should be available and its type should be "string"
    And the filter "aircrafts.manufacturer" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"

  Scenario: User basic should be able to list aircraft compatibilities
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/aircraft_compatibilities"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/aircraft_compatibility/schemas/aircraft_compatibilities.json"

  Scenario: User basic should be able to fetch an aircraft compatibility
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/aircraft_compatibilities/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/aircraft_compatibility/schemas/aircraft_compatibility.json"

  Scenario: User basic should not be allowed to create an aircraft compatibility
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/aircraft_compatibilities" with parameters:
      | key               | value              |
    Then the response status code should be 403

  @resetFileTable
  Scenario: PSE should be allowed to create an aircraft compatibility
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/aircraft_compatibilities" with parameters:
      | key         | value                  |
      | aircrafts   | ["/sales/aircrafts/1"] |
      | products    | ["/sales/products/32"] |
      | types       | ["NTO", "SIL"]         |
      | 0           | @file.pdf              |
      | 1           | @file.pdf              |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/aircraft_compatibility/schemas/aircraft_compatibility.json"
    And the JSON node "aircrafts[0].@id" should be equal to the string "/sales/aircrafts/1"
    And the JSON node "products[0].@id" should be equal to the string "/sales/products/32"
    And the JSON node "files" should have 2 elements

  Scenario: File should be pdf when creating aircraft compatibility
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/aircraft_compatibilities" with parameters:
      | key         | value                   |
      | aircrafts   | ["/sales/aircrafts/2"]  |
      | products    | ["/sales/products/28"]  |
      | types       | ["SIL"]                 |
      | 0           | @file.doc               |
    Then the response status code should be 422
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: User basic should not be allowed to update an aircraft compatibility
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/aircraft_compatibilities/8" with parameters:
      | key                          | value              |
    Then the response status code should be 403

  Scenario: User PSE of MTL should  be allowed to update an aircraft compatibility
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/aircraft_compatibilities/8" with parameters:
      | key         | value                   |
      | aircrafts   | ["/sales/aircrafts/2"]  |
      | products    | ["/sales/products/32"]  |
      | types       | ["SIL"]                 |
      | 0           | @file.pdf               |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/aircraft_compatibility/schemas/aircraft_compatibility.json"
    And the JSON node "aircrafts[0].@id" should be equal to the string "/sales/aircrafts/2"
    And the JSON node "products[0].@id" should be equal to the string "/sales/products/32"
    And the JSON node "files" should have 1 element

  Scenario: As a basic user, I can't upload a file to a non conformity
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/aircraft_compatibilities/5/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 403

  Scenario: As a PSE, I can upload a file to a non conformity
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/aircraft_compatibilities/5/files" with parameters:
      | key             | value                                         |
      | type            | SIL                                           |
      | file            | @file.pdf                                     |
    Then the response status code should be 201

  Scenario: As basic user I should be able to download a file from aircraft compatibility
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/aircraft_compatibilities/5/files/2"
    Then the response status code should be 200

  Scenario: As basic user I should not be able to delete a file from aircraft compatibility
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/aircraft_compatibilities/5/files/2"
    Then the response status code should be 403

  Scenario: As basic user I should not be able to delete a file from aircraft compatibility
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/aircraft_compatibilities/5/files/2"
    Then the response status code should be 403

  Scenario: User PSE I should be able to delete a file from aircraft compatibility
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/aircraft_compatibilities/5/files/2"
    Then the response status code should be 204

  Scenario: User basic should not be able to delete an aircraft compatibility
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/sales/aircraft_compatibilities/5"
    Then the response status code should be 403

  Scenario: PSE should be able to delete an aircraft
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/sales/aircraft_compatibilities/5"
    Then the response status code should be 204
