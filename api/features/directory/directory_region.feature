Feature: Test Directory Region API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Directory\Region" should only be available for intranet user

  Scenario: Request all regions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/regions"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_region/schemas/directory_regions.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Directory\Region" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"

  Scenario: Request a given regions - permissions OK
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/regions/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_region/schemas/directory_region.json"

  Scenario: Update a given region - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/regions/1" with the body "tests/fixtures/json/directory_region/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a given region - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/regions/1" with the body "tests/fixtures/json/directory_region/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_region/schemas/directory_region.json"

  Scenario: Create a region - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/regions" with the body "tests/fixtures/json/directory_region/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create a region
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/regions" with the body "tests/fixtures/json/directory_region/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_region/schemas/directory_region.json"

  Scenario: Create a region with the same name than an other - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/regions" with the body "tests/fixtures/json/directory_region/dummies/post_wrong.json"
    Then the response status code should be 422

  Scenario: Delete a used region - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I send a "DELETE" request to "/regions/7"
    Then the response status code should be 403

  Scenario: Delete a used region - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "DELETE" request to "/regions/7"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The region 'ALVEST' is not deletable because it is used by 1 BU (1)"

  Scenario: Delete an unused region - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    Given I send a "PUT" request to "/business_units/2" with body:
    """
    {
      "region": "/regions/2"
    }
    """
    Then the response status code should be 200
    Then I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/regions/1"
    Then the response status code should be 204
