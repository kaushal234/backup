Feature: ERP Planned Orders can be fetched through the API

  Scenario: Request Planned Orders without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/planned_orders"
    Then the response status code should be 401

  Scenario: Planned Orders should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/planned_orders?buyFromBusinessPartners=SYM0001"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Planning\OrderPlanning\PlannedOrder" is exposed on the API
    Then the filter "buyFromBusinessPartners" should be available and its type should be "string"

  Scenario: Request a collection of Planned Orders
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/planned_orders?buyFromBusinessPartners=SYM0001"
    And a "txPlannedOrders" SOAP client has been created
    And this client has been called on the operation "txList" with the following request:
    """
    {
      "ControlArea": {
          "maxNumberOfObjects": 500,
          "Filter": {
              "LogicalExpression": {
                  "logicalOperator": "and"
              }
          }
      },
      "DataArea": {
          "txPlannedOrders": {
              "itemCodeSystem": "SUP",
              "scenario": "ACT",
              "orderType": "5",
              "buyFromBusinessPartners": "SYM0001"
          }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "hydra:member[0].@id" should be equal to the string "/ion/planned_orders/000057835"
    And the JSON node "hydra:member[0].plannedOrderIdentifier" should be equal to "000057835"
    And the JSON node "hydra:member[0].status" should be equal to the string "planned"
    And the JSON node "hydra:member[0].buyFromBusinessPartner" should be equal to the string "SYM0001"
    And the JSON node "hydra:member[0].buyFromBusinessPartnerName" should be equal to the string "SYMKO"
    And the JSON node "hydra:member[0].quantity" should be equal to "7"
    And the JSON node "hydra:member[0].unitOfMeasure" should be equal to the string "EA"
    And the JSON node "hydra:member[0].itemDescription" should be equal to the string "SEAL"
    And the JSON node "hydra:member[0].price" should be equal to the number 13.05
    And the JSON node "hydra:member[0].currency" should be equal to the string "EUR"
    And the JSON node "hydra:member" should have 187 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/planned_order/schemas/collection.json"

  Scenario: Get full planned order for excel export should be possible
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/ion/planned_orders"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Location | Part Number | Supplier PN | revision | Description | Ordered Quantity | Planned Order Date | Planned Del. Date |

  Scenario: Get full monthly planned order for excel export should be possible
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/ion/planned_orders?normalizationGroups[]=planned_order:monthly"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Location | Part Number | Supplier PN | revision | Description | Planned Del. Date | Price | Ordered Quantity |

