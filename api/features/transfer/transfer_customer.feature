Feature: Test customer transfer

  Scenario: Test that I can't transfer a customer as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "PUT" request to "/sales/customers/2/transfer" with body:
    """
    {
        "target": "/sales/customers/4"
    }
    """
    Then the response status code should be 403

  Scenario: Test that I can transfer a customer
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "PUT" request to "/sales/customers/2/transfer" with body:
    """
    {
        "target": "/sales/customers/4"
    }
    """
    Then the response status code should be 200
    And the JSON node "hidden" should be true

  Scenario: Test that I can transfer a customer if used by AR
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "PUT" request to "/sales/customers/32/transfer" with body:
    """
    {
        "target": "/sales/customers/4"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "This customer is not transferable as some Account Receivables remain unpaid."

  Scenario: Test a crt has been transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/sales/customer_relationship_teams/2"
    Then the response status code should be 200
    Then the JSON node "customer.@id" should be equal to the string "/sales/customers/4"

  Scenario: Test a parent customer has been transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/sales/customers/32"
    Then the response status code should be 200
    Then the JSON node "parentCustomer.@id" should be equal to the string "/sales/customers/4"

  Scenario: Test the transferred customer has been deleted
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/sales/customers/2"
    Then the response status code should be 404
