Feature: Specification workflow

  Scenario: As basic user I can't update status to DEVELOPMENT following the workflow
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/specifications/1/status" with body:
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
    When I send a "PUT" request to "/mis/specifications/1/status" with body:
     """
    {
      "status": "DEVELOPMENT"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/specifications/schemas/specification.json"
    And the JSON node "status" should be equal to the string "DEVELOPMENT"

  Scenario: As basic user I can't update status to PRODUCTION following the workflow
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/specifications/1/status" with body:
     """
    {
      "status": "PRODUCTION"
    }
    """
    Then the response status code should be 403

  Scenario: As mis I can update status to PRODUCTION following the workflow
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/specifications/1/status" with body:
     """
    {
      "status": "PRODUCTION"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/specifications/schemas/specification.json"
    And the JSON node "status" should be equal to the string "PRODUCTION"