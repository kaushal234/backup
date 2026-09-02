Feature: Test user story

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Module\Specification\UserStory" should only be available for intranet user

  Scenario: As a basic user I can Get one user story
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/user_stories/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_stories/schemas/user_story.json"

  Scenario: As a basic user I can Get all user stories
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/user_stories"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_stories/schemas/user_stories.json"

  Scenario: As a basic user I can't POST(create) a user story
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/user_stories" with body:
    """
    {
    "category": "CREATE",
    "description": "test",
    "specification": "/mis/specifications/1"
    }
    """
    Then the response status code should be 403

  Scenario: As a MOO of the module I can't POST(create) a user story without the required properties
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/user_stories" with body:
    """
    {
      "category": null,
      "description": null,
      "specification": null
    }
    """
    Then the response status code should be 400

  Scenario: As a MKU of the module I can't POST(create) a user story without the required properties
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/user_stories" with body:
    """
    {
      "category": null,
      "description": null,
      "specification": null
    }
    """
    Then the response status code should be 400

  Scenario: As a MOO of the module I can POST(create) a user story
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/user_stories" with body:
    """
    {
      "category": "CREATE",
      "description": "test",
      "specification": "/mis/specifications/2"
    }
    """
    And the JSON node "category" should be equal to the string "CREATE"
    And the JSON node "description" should be equal to the string "test"
    And the JSON node "specification" should be equal to the string "/mis/specifications/2"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/user_stories/schemas/user_story.json"
    And the JSON node "category" should be equal to the string "CREATE"
    And the JSON node "description" should be equal to the string "test"
    And the JSON node "specification" should be equal to the string "/mis/specifications/2"
    And the JSON node "createdBy.@id" should be equal to the string "/people/11"

  Scenario: As a MKU of the module I can POST(create) a user story
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/user_stories" with body:
    """
    {
      "category": "CREATE",
      "description": "test",
      "specification": "/mis/specifications/3"
    }
    """
    And the JSON node "category" should be equal to the string "CREATE"
    And the JSON node "description" should be equal to the string "test"
    And the JSON node "specification" should be equal to the string "/mis/specifications/3"
    Then the response status code should be 201

#    basic user is LKU of module 2
  Scenario: As a LKU of the module I can POST(create) a user story
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/user_stories" with body:
    """
    {
      "category": "CREATE",
      "description": "test",
      "specification": "/mis/specifications/2"
    }
    """
    And the JSON node "category" should be equal to the string "CREATE"
    And the JSON node "description" should be equal to the string "test"
    And the JSON node "specification" should be equal to the string "/mis/specifications/2"
    Then the response status code should be 201

  Scenario: As mis I can POST(create) a user story
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/user_stories" with body:
    """
    {
      "category": "CREATE",
      "description": "test",
      "specification": "/mis/specifications/2"
    }
    """
    And the JSON node "category" should be equal to the string "CREATE"
    And the JSON node "description" should be equal to the string "test"
    And the JSON node "specification" should be equal to the string "/mis/specifications/2"
    Then the response status code should be 201

  Scenario: As basic user I can't delete a user story
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/user_stories/1"
    Then the response status code should be 403

  Scenario: As a MOO of the module I can delete a user story
    Given I authenticate as the intranet user "user-transferred@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/user_stories/2"
    Then the response status code should be 204

#    Basic user is the MKU of module 3 ODIL
  Scenario: As a MKU of the module I can delete a user story
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/user_stories/14"
    Then the response status code should be 204

#    Basic user is the LKU of module 2 2RE
  Scenario: As a LKU of the module I can delete a user story
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/user_stories/15"
    Then the response status code should be 204

  Scenario: As mis I can delete a user story
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/user_stories/16"
    Then the response status code should be 204