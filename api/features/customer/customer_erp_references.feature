Feature: Test sales customers erp references API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Sales\CustomerErpReference" should only be available for intranet user

  Scenario: Request all sales customers erp references
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customer_erp_references"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer_erp_reference/schemas/customer_erp_references.json"

  Scenario: As basic user, I can get single customer-erp references
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customer_erp_references/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer_erp_reference/schemas/customer_erp_reference.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\CustomerErpReference" is exposed on the API
    Then the filter "sso" should be available and its type should be "string"
    And the filter "customer" should be available and its type should be "string"
    And the filter "customerNumber" should be available and its type should be "string"

  Scenario: As an Superuser, I can't delete a customer-erp reference
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/customer_erp_references/3"
    Then the response status code should be 405

  Scenario: As an Superuser, I cannot add a BAAN customer-erp reference to a customer
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/customer_erp_references" with body:
     """
    {
        "sso": "/locations/23",
        "customerNumber": "AF1000",
        "customer": "/sales/customers/2"
    }
    """
    Then the response status code should be 405