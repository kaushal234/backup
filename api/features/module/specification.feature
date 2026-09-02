Feature: Test specification of module

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Module\Specification\Specification" should only be available for intranet user

  Scenario: As a basic user I can Get all specific specification
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/specifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/specifications/schemas/specifications.json"

  Scenario: As a basic user I can Get one specification
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/specifications/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/specifications/schemas/specification.json"

  Scenario: As mis user when I post a specification on a child class of module
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/specifications" with body:
    """
    {
      "module": "/extendeds/38"
    }
    """
    Then the response status code should be 422
    And the response should contain "Specification should be created only on valid module"


  Scenario: As a basic user I can't Post(create) a specification for the module
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/specifications" with body:
    """
    {
      "module": "/modules/13"
    }
    """
    Then the response status code should be 403

  Scenario: As a MOO of the module I can Post(create) a specification for the module
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/mis/specifications" with body:
    """
    {
      "module": "/modules/11"
    }
    """
    And the JSON should be valid according to the schema "tests/fixtures/json/specifications/schemas/specification.json"
    Then the response status code should be 201

  Scenario: As mis user I can't Put(update) a specification cause module relation should not be change so PUT is not available
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "PUT" request to "/mis/specifications/1" with body:
    """
    {
    }
    """
    Then the response status code should be 405

  Scenario: As mis user I can't delete a specification
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/mis/specifications/1"
    Then the response status code should be 405
