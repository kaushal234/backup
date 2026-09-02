Feature: Test country API

  Scenario: Request all countries
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/countries"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/country/schemas/countries.json"

  Scenario: Request all countries by an XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/countries"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/country/schemas/countries.json"

  Scenario: Request a single country
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/countries/6"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/country/schemas/country.json"

  Scenario: Request a single country by an XU is not permitted
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/countries/6"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/country/schemas/country.json"

  Scenario: Update a given country - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/countries/6" with the body "tests/fixtures/json/country/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a given country - permissions OK for Full Write
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/countries/6" with the body "tests/fixtures/json/country/dummies/put.json"
    Then the response status code should be 200
    And the JSON node "name" should be equal to "Elbonia"

  Scenario: Create a country - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/countries" with the body "tests/fixtures/json/country/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create a country - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/countries" with the body "tests/fixtures/json/country/dummies/post.json"
    Then the response status code should be 201
    And the JSON nodes should be equal to:
      | name             | Syldavia       |
      | alternateNames   | SУldavia       |
      | isoCode2         | SU             |
      | isoCode3         | SUD            |
      | region           | Europe         |
      | subRegion        | Eastern Europe |
    And the JSON node public should be true
    And the JSON node nbCode should be equal to the number 404

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Country" is exposed on the API
    Then the filter "legacyId" should be available and its type should be "int"
    And the filter "continent" should be available and its type should be "string"
    And the filter "public" should be available and its type should be "bool"

  Scenario: I can filter country by normalization group
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/countries?normalization_groups_override[]=country_phone_code"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/country/schemas/country_phone_code.json"
