Feature: Test Directory Juridical Location API

  Scenario: Request all juridical locations without being authenticated
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/juridical_locations"
    Then the response status code should be 401

  Scenario: Request a single juridical location without being authenticated
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/juridical_locations/1"
    Then the response status code should be 401

  Scenario: Juridical locations should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/juridical_locations"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/juridical_locations/1"
    Then the response status code should be 403

  Scenario: Request all juridical locations - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/juridical_locations"
    Then the response status code should be 403

  Scenario: Request all juridical locations - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/juridical_locations"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_juridical_location/schemas/directory_juridical_locations.json"

  Scenario: Request all juridical locations for SOR module
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/juridical_locations"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_juridical_location/schemas/directory_juridical_locations.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Directory\JuridicalLocation" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"
    And the filter "id" should be available and its type should be "int"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "locations[]" should be available and its type should be "string"

  Scenario: Request a given juridical locations - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I send a "GET" request to "/juridical_locations/1"
    Then the response status code should be 403

  Scenario: Request a given juridical locations - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/juridical_locations/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_juridical_location/schemas/directory_juridical_location.json"

  Scenario: Update a given juridical locations - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/juridical_locations/1" with the body "tests/fixtures/json/directory_juridical_location/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a given juridical locations - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/juridical_locations/1" with the body "tests/fixtures/json/directory_juridical_location/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_juridical_location/schemas/directory_juridical_location.json"

  Scenario: Create a juridical locations - permissions OK
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/juridical_locations" with the body "tests/fixtures/json/directory_juridical_location/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create a juridical locations - insufficient permissions
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/juridical_locations" with the body "tests/fixtures/json/directory_juridical_location/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_juridical_location/schemas/directory_juridical_location.json"

  Scenario: Create a juridical locations with wrong address - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/juridical_locations" with the body "tests/fixtures/json/directory_juridical_location/dummies/post_wrong.json"
    Then the response status code should be 400

  Scenario: Delete a juridical locations - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/juridical_locations/2"
    Then the response status code should be 403

  Scenario: Delete a juridical locations - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/juridical_locations/2"
    Then the response status code should be 204

  Scenario: Delete a used juridical location
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/juridical_locations/1"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The juridical location 'Bernay' is not deletable because it is used by 31 Locations (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 32)"
