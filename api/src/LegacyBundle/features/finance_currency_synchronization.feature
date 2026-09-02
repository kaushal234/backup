Feature: Test currencies double write API

  Scenario: Create a currency
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/finance/currencies" with body:
    """
      {
        "name": "SIM"
      }
    """
    Then the response status code should be 201
    And the column "list_name" from the "lists" legacy table has been inserted with string "list.common.currency"
    And the column "list_key" from the "lists" legacy table has been inserted with string ""
    And the column "list_item" from the "lists" legacy table has been inserted with string "SIM"
    And a new row has been inserted in the legacy table "lists"