Feature: User story workflow

  Scenario: As basic user I can't update status to PLANNED following the workflow
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/user_stories/4/status" with body:
     """
    {
      "status": "PLANNED"
    }
    """
    Then the response status code should be 403

  Scenario: As mis I can update status to PLANNED following the workflow
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/user_stories/4/status" with body:
     """
    {
      "status": "PLANNED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_stories/schemas/user_story.json"
    And the JSON node "status" should be equal to the string "PLANNED"

  Scenario: As basic user I can't update status to DEVELOPMENT following the workflow
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/user_stories/4/status" with body:
     """
    {
      "status": "DEVELOPMENT"
    }
    """
    Then the response status code should be 403

  Scenario: As mis I can update status to DEVELOPMENT following the workflow
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/user_stories/4/status" with body:
     """
    {
      "status": "DEVELOPMENT"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_stories/schemas/user_story.json"
    And the JSON node "status" should be equal to the string "DEVELOPMENT"

  Scenario: As basic user I can't update status to TESTING following the workflow
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/user_stories/4/status" with body:
     """
    {
      "status": "TESTING"
    }
    """
    Then the response status code should be 403

  Scenario: As mis I can update status to TESTING following the workflow
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/user_stories/4/status" with body:
     """
    {
      "status": "TESTING"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_stories/schemas/user_story.json"
    And the JSON node "status" should be equal to the string "TESTING"

  Scenario: As basic user I can't update status to VALIDATED following the workflow
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/user_stories/4/status" with body:
     """
    {
      "status": "VALIDATED"
    }
    """
    Then the response status code should be 403

  Scenario: As mis I can update status to VALIDATED following the workflow
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/user_stories/4/status" with body:
     """
    {
      "status": "VALIDATED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_stories/schemas/user_story.json"
    And the JSON node "status" should be equal to the string "VALIDATED"

  Scenario: As basic user I can't update status to HAS BEEN EDITED following the workflow
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/user_stories/4/status" with body:
     """
    {
      "status": "HAS BEEN EDITED"
    }
    """
    Then the response status code should be 403


  Scenario: As mis I can update status to HAS BEEN EDITED following the workflow
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/user_stories/4/status" with body:
     """
    {
      "status": "HAS BEEN EDITED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_stories/schemas/user_story.json"
    And the JSON node "status" should be equal to the string "HAS BEEN EDITED"
    Given I authenticate as the intranet user "user-mis@tld.fr"
    When I send a "GET" request to "mis/specifications/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/specifications/schemas/specification.json"
    And the JSON node "status" should be equal to the string "DEVELOPMENT"

  Scenario: When I change all user story status to VALIDATED the specification status should change to PRODUCTION
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/user_stories/11/status" with body:
     """
    {
      "status": "VALIDATED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_stories/schemas/user_story.json"
    And the JSON node "status" should be equal to the string "VALIDATED"
    Given I authenticate as the intranet user "user-mis@tld.fr"
    When I send a "GET" request to "mis/specifications/4"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/specifications/schemas/specification.json"
    And the JSON node "status" should be equal to the string "PRODUCTION"

  Scenario: As mis when I delete all user story the specification status should change to DEVELOPMENT
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/user_stories/11"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-mis@tld.fr"
    When I send a "GET" request to "mis/specifications/4"
    And the JSON should be valid according to the schema "tests/fixtures/json/specifications/schemas/specification.json"
    And the JSON node "status" should be equal to the string "DEVELOPMENT"

  Scenario: As authorized user if specification is in Production and I create a new User story the specification status should change to DEVELOPMENT
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/user_stories/12/status" with body:
     """
    {
      "status": "VALIDATED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_stories/schemas/user_story.json"
    And the JSON node "status" should be equal to the string "VALIDATED"
    Given I authenticate as the intranet user "user-mis@tld.fr"
    When I send a "GET" request to "mis/specifications/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/specifications/schemas/specification.json"
    And the JSON node "status" should be equal to the string "PRODUCTION"
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/user_stories" with body:
    """
    {
      "category": "CREATE",
      "description": "test",
      "specification": "/mis/specifications/5"
    }
    """
    And the JSON node "category" should be equal to the string "CREATE"
    And the JSON node "description" should be equal to the string "test"
    And the JSON node "specification" should be equal to the string "/mis/specifications/5"
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-mis@tld.fr"
    When I send a "GET" request to "mis/specifications/4"
    And the JSON should be valid according to the schema "tests/fixtures/json/specifications/schemas/specification.json"
    And the JSON node "status" should be equal to the string "DEVELOPMENT"