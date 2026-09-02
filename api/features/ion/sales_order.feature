Feature: Test Sales Order API

  Scenario: Request a single Sales Order without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/sales_orders/SO0000001"
    Then the response status code should be 401

  Scenario: Sales Orders should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/sales_orders/SO0000001"
    Then the response status code should be 403

  Scenario: Request a collection of Sales Orders is not implemented
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/sales_orders"
    Then the response status code should be 404

  Scenario: Request a single Sales Order
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/sales_orders/SO0000001"
    Then the response status code should be 200
    And a "SalesOrder" SOAP client has been created
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "SalesOrder": {
          "salesOrder": "SO0000001"
        }
      }
    }
    """
    And a total of 1 request has been sent to ION
    And the JSON node "salesOrder" should be equal to "SO0000001"
    And the JSON node "lines" should have 1 element
    And the JSON node "lines[0].lineID" should be equal to "10"
    And the JSON node "lines[0].item" should be equal to "1110149"
    And the JSON node "lines[0].plannedDeliveryDate" should be equal to "2022-11-11T20:14:00+00:00"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/sales_order/schemas/sales_order.json"


