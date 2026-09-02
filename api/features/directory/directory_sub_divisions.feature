Feature: Test Directory SubDivisions API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Directory\SubDivision" should only be available for intranet user

  Scenario: Request all subdivisions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sub_divisions?order[name]=ASC"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_sub_division/schemas/directory_sub_divisions.json"

  Scenario: Request a given subdivisions - permissions OK
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sub_divisions/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_sub_division/schemas/directory_sub_division.json"

  Scenario: Update a given subdivision - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sub_divisions/1" with body:
    """
    {
      "name": "divide and rule"
    }
    """
    Then the response status code should be 403

  Scenario: Update a given subdivision - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sub_divisions/1" with body:
    """
    {
      "name": "divide and rule"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_sub_division/schemas/directory_sub_division.json"
    And the JSON node "name" should be equal to "divide and rule"

  Scenario: Create a subdivision - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sub_divisions" with body:
    """
    {
      "name": "vide"
    }
    """
    Then the response status code should be 403

  Scenario: Create a subdivision
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sub_divisions" with body:
    """
    {
      "name": "vide",
      "division": "/divisions/1"
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to the string "/sub_divisions/5"
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_sub_division/schemas/directory_sub_division.json"

  Scenario: Create a subdivision with the same name than an other - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sub_divisions" with body:
    """
    {
      "name": "vide",
      "division": "/divisions/1"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "name: This value is already used."

  Scenario: Delete a used subdivision - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I send a "DELETE" request to "/sub_divisions/1"
    Then the response status code should be 403

  Scenario: Delete a subdivision used by a region - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "DELETE" request to "/sub_divisions/1"
    Then the response status code should be 422
    And the JSON node "hydra:description" should match "/^The sub division 'divide and rule' is not deletable because it is used by \d+ Region \(.*\)$/"

  Scenario: Delete an unused subdivision - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sub_divisions/5"
    Then the response status code should be 204

  @resetFileTable
  Scenario: Upload a logo in sub division
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sub_divisions/2/logo" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201

  Scenario: Read a logo in sub division with basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sub_divisions/2/logo/1"
    Then the response status code should be 200

  Scenario: Delete a logo in sub division
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sub_divisions/2/logo/1"
    Then the response status code should be 204
