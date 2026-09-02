Feature: Test Directory Department API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Directory\Department" should only be available for intranet user

  Scenario: Request all departments
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/departments"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_department/schemas/directory_departments.json"

  Scenario: Request a given department
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/departments/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_department/schemas/directory_department.json"

  Scenario: Update a given department with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/departments/1" with the body "tests/fixtures/json/directory_department/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a given department with permission OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/departments/1" with the body "tests/fixtures/json/directory_department/dummies/put.json"
    Then the response status code should be 200

  Scenario: Create a department with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/departments" with the body "tests/fixtures/json/directory_department/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create a department with permission OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/departments" with the body "tests/fixtures/json/directory_department/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_department/schemas/directory_department.json"

  Scenario: Create a department with the same name than an other
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/departments" with the body "tests/fixtures/json/directory_department/dummies/post.json"
    Then the response status code should be 422

  Scenario: Delete a department without any users affected to it
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/departments/1"
    Then the response status code should be 204

  Scenario: Delete a used department
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/departments/2"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The department '2' is not deletable because it is used by 2 PEOPLE (28, 128)"

  Scenario: Delete a department with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/departments/2"
    Then the response status code should be 403

  Scenario: Delete a used department with a wrong replacement
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/departments/2" with the body "tests/fixtures/json/directory_department/dummies/delete_wrong.json"
    Then the response status code should be 422
    And the JSON node "hydra:description" should contain 'An instance of "Department" was expected'

  Scenario: Delete a used department with a right replacement
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/departments/2" with the body "tests/fixtures/json/directory_department/dummies/delete.json"
    Then the response status code should be 204

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Directory\Department" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "name" should be available and its type should be "string"
