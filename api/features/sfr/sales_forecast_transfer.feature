Feature: Sales Forecasts can be transferred following multiple criteria

  Scenario: A basic user can't transfer SFR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "POST" request to "/sales/sales_forecasts/transfer" with body:
    """
    """
    Then the response status code should be 403

  Scenario: To transfer SFR, the SSO is mandatory
    Given I authenticate as the intranet user "user-evp@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "POST" request to "/sales/sales_forecasts/transfer" with body:
    """
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "sso"
    And the JSON node "violations[0].message" should contain "This value should not be null."
    And the JSON node "violations[1].propertyPath" should be equal to "asmTarget"
    And the JSON node "violations[1].message" should contain "This value should not be null."

  Scenario: Successful transfer
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "GET" request to "/sales/sales_forecasts/1"
    Then the JSON node "asm.@id" should be equal to the string "/people/31"
    Then the JSON node "sso.@id" should be equal to the string "/locations/23"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "POST" request to "/sales/sales_forecasts/transfer" with body:
    """
    {
      "sso": "/locations/23",
      "asmSource": "/people/31",
      "asmTarget": "/people/38",
      "country": "/countries/1",
      "buyer": "/sales/customers/1",
      "endUser": "/sales/customers/35"
    }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "GET" request to "/sales/sales_forecasts/1"
    Then the JSON node "asm.@id" should be equal to the string "/people/38"
    # transfer it back to the original ASM for the tests
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    And I send a "POST" request to "/sales/sales_forecasts/transfer" with body:
    """
    {
      "sso": "/locations/23",
      "asmSource": "/people/38",
      "asmTarget": "/people/31",
      "country": "/countries/1",
      "buyer": "/sales/customers/1",
      "endUser": "/sales/customers/35"
    }
    """
    Then the response status code should be 200
