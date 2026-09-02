Feature: Test IATA Codes API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\IATACode" should only be available for intranet user

  Scenario: Request all IATA Codes
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/iata_codes"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/iata_code/schemas/iata_codes.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\IATACode" is exposed on the API
    Then the filter "order[code]" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "code" should be available and its type should be "string"
    And the filter "cityCode3" should be available and its type should be "string"
    And the filter "cityName" should be available and its type should be "string"
    And the filter "country" should be available and its type should be "string"
    And the filter "type" should be available and its type should be "string"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Common\Airport" is exposed on the API
    Then the filter "order[code]" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "code" should be available and its type should be "string"
    And the filter "cityCode3" should be available and its type should be "string"
    And the filter "cityName" should be available and its type should be "string"
    And the filter "country" should be available and its type should be "string"
    And the filter "type" should be available and its type should be "string"
    And the filter "exists[serviceAreas]" should be available and its type should be "bool"
    And the filter "exists[country]" should be available and its type should be "bool"
    And the filter "q" should be available and its type should be "string"
    And the filter "normalization_groups_override[]" should be available and its type should be "string"
    And the query parameter "autocomplete" should be available

  Scenario: Request a single IATA Code
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/iata_codes/6"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/iata_code/schemas/iata_code.json"

  Scenario: Airports collection should be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/airports"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/iata_code/schemas/iata_codes.json"
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/airports/66"
    Then the response status code should be 200

  Scenario: Request all airports
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/airports"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/iata_code/schemas/iata_codes.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\IATACode" is exposed on the API
    Then the filter "legacyId" should be available and its type should be "int"

  Scenario: I can search airports
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/airports?q=PAR"
    Then the response status code should be 200

  Scenario: Request a single airport
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/airports/66"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/iata_code/schemas/iata_code.json"

  Scenario: Update an IATA Code should not be allowed
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/iata_codes/1"
    Then the response status code should be 405

  Scenario: Create an IATA Code should not be allowed
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/iata_codes"
    Then the response status code should be 405

  Scenario: Create an airport - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/airports" with the body "tests/fixtures/json/iata_code/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create an airport
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/airports" with the body "tests/fixtures/json/iata_code/dummies/post.json"
    Then the response status code should be 201
    And the JSON node "type" should be equal to "Airport"
    And the JSON node "cityCode3" should be equal to "TXY"
    And the JSON node "cityName" should be equal to "Toxicity"
    And the JSON node "state" should be equal to "ATWA"
    And the JSON node "country.@id" should be equal to "/countries/2"
    And the JSON node "code" should be equal to "SOAD"
    And the JSON node "name" should be equal to "Lonely Day"
    And the JSON node "source" should be equal to "IATA"

  Scenario: Could not duplicate the code/cityName
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/airports" with the body "tests/fixtures/json/iata_code/dummies/post.json"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "code"
    And the JSON node "violations[0].message" should contain 'This value is already used.'

  Scenario: Create an heliport with the same data should work
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/heliports" with the body "tests/fixtures/json/iata_code/dummies/post.json"
    Then the response status code should be 201
    And the JSON node "type" should be equal to "Heliport"
    And the JSON node "cityCode3" should be equal to "TXY"
    And the JSON node "cityName" should be equal to "Toxicity"
    And the JSON node "state" should be equal to "ATWA"
    And the JSON node "country.@id" should be equal to "/countries/2"
    And the JSON node "code" should be equal to "SOAD"
    And the JSON node "name" should be equal to "Lonely Day"
    And the JSON node "source" should be equal to "IATA"

  Scenario: Update a given airport - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/airports/61" with the body "tests/fixtures/json/iata_code/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a given airport - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/airports/61" with the body "tests/fixtures/json/iata_code/dummies/put.json"
    Then the response status code should be 200
    And the JSON node "type" should be equal to "Airport"
    And the JSON node "name" should be equal to "Lonely Day"

  Scenario: Create a railway station
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/railway_stations" with the body "tests/fixtures/json/iata_code/dummies/post.json"
    Then the response status code should be 201
    And the JSON node "type" should be equal to "Railway Station"

  Scenario: Create a bus station
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/bus_stations" with the body "tests/fixtures/json/iata_code/dummies/post.json"
    Then the response status code should be 201
    And the JSON node "type" should be equal to "Bus Station"

  Scenario: Create an Off-Line Point
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/off_line_points" with the body "tests/fixtures/json/iata_code/dummies/post.json"
    Then the response status code should be 201
    And the JSON node "type" should be equal to "Off-Line Point"

  Scenario: Create a Ferry Port
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/ferry_ports" with the body "tests/fixtures/json/iata_code/dummies/post.json"
    Then the response status code should be 201
    And the JSON node "type" should be equal to "Ferry Port"

  Scenario: As user-basic, I should not be allowed to add a new airport IATA code
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/airports" with body:
    """
    {
      "type": "Spatioport",
      "cityCode3": "MOS",
      "cityName": "Mos Eisley",
      "state": Tatooine,
      "country": null,
      "code": "MOSAP",
      "name": "Mos Eisley Spatioport",
      "source": "TLD"
    }
    """
    Then the response status code should be 403

  Scenario: As user-basic, I should not be allowed to edit an airport IATA code
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/airports/61" with body:
    """
    {
      "type": "Airport",
      "cityCode3": "BDX",
      "cityName": "Bordeaux",
      "state": null,
      "country": null,
      "code": "BDX",
      "name": "Mérignac",
      "source": "IATA"
    }
    """
    Then the response status code should be 403

  Scenario: As user-coo, who is Operational Owner of ER Module, I Should be allowed to add a new airport IATA code
    Given I authenticate as the intranet user "user-coo@tld.fr"
    And I add "Content-type" header equal to "text/plain"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/airports" with body:
    """
    {
      "type": "Spatioport",
      "cityCode3": "MOS",
      "cityName": "Mos Eisley",
      "state": "Tatooine",
      "country": null,
      "code": "MOSAP",
      "name": "Mos Eisley Spatioport",
      "source": "TLD"
    }
    """
    Then the response status code should be 201

  Scenario: As user-coo, who is Operational Owner of ER Module, I Should be allowed to edit an airport IATA code
    Given I authenticate as the intranet user "user-coo@tld.fr"
    And I add "Content-type" header equal to "text/plain"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/airports/61" with body:
    """
    {
      "type": "Airport",
      "cityCode3": "BDX",
      "cityName": "Bordeaux",
      "state": null,
      "country": null,
      "code": "BDX",
      "name": "Mérignac",
      "source": "IATA"
    }
    """
    Then the response status code should be 200
