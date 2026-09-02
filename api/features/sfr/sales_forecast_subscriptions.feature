Feature: Sales Forecasts can have subscribers

  Scenario: Create a subscription on SFR requires to be granted EDIT permissions on the given SFR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/subscriptions" with body:
    """
    {
      "resource": "/sales/sales_forecasts/1",
      "user": "/people/11"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/subscriptions" with body:
    """
    {
      "resource": "/sales/sales_forecasts/1",
      "user": "/people/11"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/subscription/schemas/subscription.json"

  Scenario: Delete a subscription on SFR requires to be granted EDIT permissions on the given SFR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/subscriptions/4"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/subscriptions/4"
    Then the response status code should be 204
