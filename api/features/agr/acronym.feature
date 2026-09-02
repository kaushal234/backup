Feature: Test acronym API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Acronym" should only be available for intranet user

  Scenario: Request all acronym without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronyms"
    Then the response status code should be 401

  Scenario: Request a single acronym without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronyms/1"
    Then the response status code should be 401

  Scenario: Acronyms should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronyms"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronyms/1"
    Then the response status code should be 403

  Scenario: Request all acronyms
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronyms"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/acronym/schemas/acronyms.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Acronym" is exposed on the API
    Then the filter "order[acronym]" should be available and its type should be "string"
    And the filter "acronym" should be available and its type should be "string"
    And the filter "description" should be available and its type should be "string"
    And the filter "shortDescription" should be available and its type should be "string"
    And the filter "categories" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"

  Scenario: A user can use the simple search in acronyms
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronyms?q=MIS"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/acronym/schemas/acronyms.json"

    Then I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronyms?q=Information"
    Then the response status code should be 200

    Then I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And the JSON should be valid according to the schema "tests/fixtures/json/acronym/schemas/acronyms.json"
    When I send a "GET" request to "/acronyms?q=involve"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/acronym/schemas/acronyms.json"

  Scenario: Request a single acronym
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronyms/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/acronym/schemas/acronym.json"

  Scenario: A basic user can't add an acronym
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/acronyms" with the body "tests/fixtures/json/acronym/dummies/post.json"
    Then the response status code should be 403

  Scenario: An advanced user can add an acronym
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/acronyms" with the body "tests/fixtures/json/acronym/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/acronym/schemas/acronym.json"

  Scenario: An advanced user can't add an existing acronym
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/acronyms" with the body "tests/fixtures/json/acronym/dummies/post.json"
    Then the response status code should be 422

  Scenario: A basic user can't update an acronym
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/acronyms/1" with the body "tests/fixtures/json/acronym/dummies/put.json"
    Then the response status code should be 403

  Scenario: An advanced user can update an acronym
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/acronyms/1" with the body "tests/fixtures/json/acronym/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/acronym/schemas/acronym.json"

  Scenario: A basic user can't delete an acronym
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/acronyms/1"
    Then the response status code should be 403

  Scenario: An advanced user can delete an acronym
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/acronyms/1"
    Then the response status code should be 204

  Scenario: I can filter acronym by legacy id
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronyms?legacyId=99"
    Then the response status code should be 200

  Scenario: Request all acronyms filtered by normalization group for export
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/acronyms?normalizationGroupsOverride[]=acronym_export"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/acronym/schemas/acronyms_export.json"

  Scenario: Acronyms can be downloaded as an Excel file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/acronyms?columns=acronym,description,shortDescription"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Acronym | Description | Short Description |
    And the xlsx file should have 3 lines
