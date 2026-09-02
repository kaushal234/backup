Feature: Test Purchase Order Statistic API

  Scenario: Request purchase order statistic for superuser with permissions
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/purchase_order_statistics"
    Then the response status code should be 200
    And a "txPurchaseOrder" SOAP client has been created
    And this client has been called on the operation "txLateAndUnconfirmedLines" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 500,
        "Filter": {
            "LogicalExpression": {
                "logicalOperator": "and"
            }
        }
      }
    }
    """
    And the JSON should be valid according to the schema "tests/fixtures/json/purchase_order/schemas/purchase_order_statistics.json"
    And a total of 1 request has been sent to ION