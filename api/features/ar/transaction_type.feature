Feature: Test transaction types

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Finance\TransactionType" should only be available for intranet user

  Scenario: Request all transaction types
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/transaction_types"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/transaction_type/schemas/transaction_types.json"

  Scenario: Request a single transaction type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/transaction_types/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/transaction_type/schemas/transaction_type.json"

  Scenario: Create a transaction type should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/finance/transaction_types" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: Update a transaction type should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/finance/transaction_types/1" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: Delete a transaction type should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/finance/transaction_types/1"
    Then the response status code should be 405

  Scenario: Request all transaction types using filters
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/transaction_types?order[name]=asc"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/transaction_type/schemas/transaction_types.json"
