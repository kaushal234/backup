Feature: Test security levels API
  Scenario: Request all security levels
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/security_levels"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/security_levels.json"
    And the JSON node "hydra:totalItems" should be equal to "4"

  Scenario: Request a single security level
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/security_levels/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/security_level.json"
    And the JSON node "@id" should be equal to the string "/security_levels/1"
    And the JSON node "@type" should be equal to the string "SecurityLevel"
    And the JSON node "name" should be equal to the string "Iso27"
    And the JSON node "description" should be equal to the string "Test description Iso27"

  Scenario: Add a security level shouldn't be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/security_levels" with body:
    """
    {
      "name": "test new security level"
    }
    """
    Then the response status code should be 403

  Scenario: As an allowed user, I can add a security level
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/security_levels" with body:
    """
    {
      "name": "test new",
      "description": "test description"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/security_level.json"
    And the JSON node "@id" should be equal to the string "/security_levels/5"
    And the JSON node "name" should be equal to the string "test new"
    And the JSON node "description" should be equal to the string "test description"

  Scenario: Edit a security level
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/security_levels/2" with body:
    """
    {
      "name": "test edit"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/security_level.json"
    And the JSON node "name" should be equal to the string "test edit"