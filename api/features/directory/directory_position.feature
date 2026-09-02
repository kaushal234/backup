Feature: Test Directory Position API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Directory\Position" should only be available for intranet user

  Scenario: Request all positions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/positions"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_position/schemas/directory_positions.json"

  Scenario: Request a given position
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/positions/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_position/schemas/directory_position.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Directory\Position" is exposed on the API
    Then the filter "legacyId" should be available and its type should be "int"
    And the filter "users.businessUnit" should be available and its type should be "string"
    And the filter "users.disabled" should be available and its type should be "bool"
    And the filter "q" should be available and its type should be "string"

  Scenario: Update a given position with no permission
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/positions/1" with the body "tests/fixtures/json/directory_position/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a given position with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/positions/1" with the body "tests/fixtures/json/directory_position/dummies/put.json"
    Then the response status code should be 200

  Scenario: Create a position with wrong level
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/positions" with the body "tests/fixtures/json/directory_position/dummies/post_wrong.json"
    Then the response status code should be 400

  Scenario: Create a position with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/positions" with the body "tests/fixtures/json/directory_position/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/directory_position/schemas/directory_position.json"

  Scenario: Create a position with no permission
    Given I authenticate as the intranet user "user-hr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/positions" with the body "tests/fixtures/json/directory_position/dummies/post.json"
    Then the response status code should be 403

  Scenario: Delete a position with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/positions/12"
    Then the response status code should be 403

  Scenario: Delete a position with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/positions/11"
    Then the response status code should be 204

  Scenario: Delete a position
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/positions/1"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The position 'this is my custom position' is not deletable because it is used by 8 people (11, 48, 110, 118, 122, 123, 124, 125)"
