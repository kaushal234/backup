Feature: Test contract types can be created and updated

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Directory\ContractType" should only be available for intranet user

  Scenario: contract types should be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contract_types?order[name]=asc"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract_type/schemas/contract_types.json"

  Scenario: contract type detail should be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/contract_types/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract_type/schemas/contract_type.json"

  Scenario: Create a contract type should not be possible for user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/contract_types" with body:
    """
    {
      "name": "TEST",
      "description": "just a test description"
    }
    """
    Then the response status code should be 403

  Scenario: Create a contract type should be possible for user gtcd
    Given I authenticate as the intranet user "user-gtcd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/contract_types" with body:
    """
    {
      "name": "TEST",
      "description": "just a test description"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/contract_type/schemas/contract_type.json"

  Scenario: Update a contract type should not be possible for user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contract_types/3" with body:
    """
    {
      "name": "TESTASSE",
    }
    """
    Then the response status code should be 403

  Scenario: Update a contract type should be possible for user gtcd
    Given I authenticate as the intranet user "user-gtcd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/contract_types/3" with body:
    """
    {
      "name": "TESTASSE",
      "description": "description updated"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/contract_type/schemas/contract_type.json"
    And the JSON node "name" should be equal to the string "TESTASSE"
    And the JSON node "description" should be equal to the string "description updated"

  Scenario: Delete a contract type should not be possible for user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contract_types/3"
    Then the response status code should be 403

  Scenario: Delete a contract type should  possible for user gtcd
    Given I authenticate as the intranet user "user-gtcd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/contract_types/3"
    Then the response status code should be 204

