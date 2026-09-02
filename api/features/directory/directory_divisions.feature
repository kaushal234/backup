Feature: Test Directory Divisions API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Directory\Division" should only be available for intranet user

  Scenario: Request all divisions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/divisions?order[name]=ASC"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_division/schemas/directory_divisions.json"

  Scenario: Request all divisions with the full tree
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/divisions?normalizationGroups[]=division:tree"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_division/schemas/directory_divisions_tree.json"

  Scenario: Request a given divisions - permissions OK
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/divisions/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_division/schemas/directory_division.json"
    And the JSON node "representatives" should have 2 elements

  Scenario: Update a given division - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/divisions/1" with body:
    """
    {
      "name": "divide and rule"
    }
    """
    Then the response status code should be 403

  Scenario: Update a given division - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/divisions/1" with body:
    """
    {
      "name": "divide and rule"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_division/schemas/directory_division.json"
    And the JSON node "name" should be equal to "divide and rule"

  Scenario: Update the representatives of a given division - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/divisions/1" with body:
    """
    {
      "representatives": ["/people/11"]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_division/schemas/directory_division.json"
    And the JSON node "representatives" should have 1 element

  Scenario: Create a division - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/divisions" with body:
    """
    {
      "name": "vide"
    }
    """
    Then the response status code should be 403

  Scenario: Create a division
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/divisions" with body:
    """
    {
      "name": "vide"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_division/schemas/directory_division.json"

  Scenario: Create a division with representatives - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/divisions" with body:
    """
    {
      "name": "represented",
      "representatives": ["/people/11", "/people/12"]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_division/schemas/directory_division.json"
    And the JSON node "representatives" should have 2 elements

  Scenario: Create a division with the same name than an other - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/divisions" with body:
    """
    {
      "name": "vide"
    }
    """
    Then the response status code should be 422

  Scenario: Delete a used division - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I send a "DELETE" request to "/divisions/1"
    Then the response status code should be 403

  Scenario: Delete a used division - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "DELETE" request to "/divisions/1"
    Then the response status code should be 422
    And the JSON node "hydra:description" should match "/^The division 'divide and rule' is not deletable because it is used by \d+ SubDivision \(.*\)$/"

  Scenario: Delete an unused division - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/divisions/5"
    Then the response status code should be 204
