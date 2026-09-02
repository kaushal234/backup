Feature: Test Group API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Group" should only be available for intranet user

  Scenario: Request all user groups
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/groups"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis_group/schemas/groups.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Group" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "restricted" should be available and its type should be "bool"
    And the filter "name" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"

  Scenario: Request a given user group
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/groups/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis_group/schemas/group.json"

  Scenario: A standard user can't add a group
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/groups" with the body "tests/fixtures/json/mis_group/dummies/post.json"
    Then the response status code should be 403

  Scenario: A  superuser can create a user group
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/groups" with the body "tests/fixtures/json/mis_group/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/mis_group/schemas/group.json"
    And the JSON nodes should be equal to:
      | name                 | AWESOME_GROUP                          |
      | description          | An awesome group description           |
      | legacyPermissions    | you have permissions to do everything  |

  Scenario: Create a user group with the same name than an other
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/groups" with the body "tests/fixtures/json/mis_group/dummies/post.json"
    Then the response status code should be 422

  Scenario: basic users can't delete an unused user group
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/groups/89"
    Then the response status code should be 403

  Scenario: Delete an unused user group
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/groups/89"
    Then the response status code should be 204

  Scenario: Delete a used user group
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/groups/1"
    Then the response status code should be 422
    And the JSON node "hydra:description" should match "#The group 'SUPERUSER' is not deletable because it is used by#"

  Scenario: A standard user can't update a group
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/groups/49" with the body "tests/fixtures/json/mis_group/dummies/put.json"
    Then the response status code should be 403

  Scenario: A superuser can update a group
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/groups/51" with the body "tests/fixtures/json/mis_group/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis_group/schemas/group.json"
    Then I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/groups/51" with body:
    """
    {
        "name": "GG_GOOD_GAME",
        "legacyPermissions": "you have no permission"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis_group/schemas/group.json"
    And the JSON node "legacyPermissions" should be equal to the string "you have no permission"

  Scenario: A superuser can't link/unlink feature to a group
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/groups/49"
    Then the response status code should be 200
    And the JSON node "features" should have 13 elements
    Then I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/groups/49" with body:
    """
    {
        "features": ["\/features\/1", "\/features\/2", "\/features\/3"]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/mis_group/schemas/group.json"
    Then I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/groups/49"
    Then the response status code should be 200
    And the JSON node "features" should have 13 elements
