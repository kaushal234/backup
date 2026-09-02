Feature: Test Directory Business Unit

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Directory\BusinessUnit" should only be available for intranet user

  Scenario: Request all business units
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/business_units"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_business_unit/schemas/directory_business_units.json"

  Scenario: Request a given business unit
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/business_units/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_business_unit/schemas/directory_business_unit.json"

  Scenario: Update a given business unit
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/business_units/3" with the body "tests/fixtures/json/directory_business_unit/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_business_unit/schemas/directory_business_unit.json"

  Scenario: Create a business units
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/business_units" with the body "tests/fixtures/json/directory_business_unit/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_business_unit/schemas/directory_business_unit.json"

  Scenario: Create a business units with the same name than an other
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/business_units" with the body "tests/fixtures/json/directory_business_unit/dummies/post_wrong.json"
    Then the response status code should be 422

  Scenario: Delete a business units
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/business_units/19"
    Then the response status code should be 204

  Scenario: Delete a business unit used by a people
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/business_units/1"
    Then the response status code should be 422
    And the JSON node "hydra:description" should match "/^The business unit 'SAY_MY_NAME' is not deletable because it is used by \d+ PEOPLE \(.*\)$/"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Directory\BusinessUnit" is exposed on the API
    Then the filter "location.legacyId" should be available and its type should be "int"
    And the filter "region" should be available and its type should be "string"
    And the filter "order[name]" should be available and its type should be "string"
    And the filter "location" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"
