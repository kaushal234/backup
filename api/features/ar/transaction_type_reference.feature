Feature: Test transaction type references

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Finance\TransactionTypeReference" should only be available for intranet user

  Scenario: Request all transaction type references
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/transaction_type_references"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/transaction_type_reference/schemas/transaction_type_references.json"

  Scenario: Request a single transaction type reference
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/transaction_type_references/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/transaction_type_reference/schemas/transaction_type_reference.json"

  Scenario: Create a transaction type reference should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/finance/transaction_type_references" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: Update a transaction type reference should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/finance/transaction_type_references/1" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: Delete a transaction type reference should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/finance/transaction_type_references/1"
    Then the response status code should be 405

  Scenario: Request all transaction type references using filters
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/transaction_type_references?location=/locations/23&erpType=CLU"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/transaction_type_reference/schemas/transaction_type_references.json"
