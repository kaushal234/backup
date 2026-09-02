Feature: Test Shipment API

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Warehousing\Shipments\Shipment" is exposed on the API
    Then the filter "order" should be available and its type should be "string"

  Scenario: Request all Shipments
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/shipments?order=220000010"
    Then the response status code should be 200
    And a "txShipments" SOAP client has been created
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
        "txShipments": {
          "order": "220000010",
          "orderOrigins": "1|2|3"
        }
      }
    }
    """
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/shipment/schemas/shipments.json"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].@id" should be equal to the string "/ion/shipments/OS0001325"
    And the JSON node "hydra:member[0].shipment" should be equal to the string "OS0001325"
    And the JSON node "hydra:member[0].status" should be equal to the string "confirmed"
    And the JSON node "hydra:member[0].procedure" should be equal to "S11"
    And the JSON node "hydra:member[0].load.loadCode" should be equal to the string "L00001052"
    And the JSON node "hydra:member[0].load.route" should exist
    And the JSON node "hydra:member[0].load.status" should be equal to the string "confirmed"
    And the JSON node "hydra:member[0].load.plannedDeliveryDate" should be equal to the string "2022-07-27T08:06:00Z"
    And the JSON node "hydra:member[0].load.trackingNumber" should exist
    And the JSON node "hydra:member[0].load.carrier.code" should be equal to the string "F04"
    And the JSON node "hydra:member[0].load.carrier.name" should be equal to the string "FedEx Standard Overnight"
    And the JSON node "hydra:member[0].load.carrier.url" should exist
    And the JSON node "hydra:member[0].shipFrom.plannedDeliveryDate" should be equal to the string "2022-07-27T08:06:00+00:00"
    And the JSON node "hydra:member[0].shipFrom.type" should be equal to the string "warehouse"
    And the JSON node "hydra:member[0].shipFrom.code" should be equal to the string "220FG1"
    And the JSON node "hydra:member[0].shipFrom.address" should be equal to the string "W00000220"
    And the JSON node "hydra:member[0].shipTo.plannedReceiptDate" should be equal to the string "2022-07-27T08:06:00+00:00"
    And the JSON node "hydra:member[0].shipTo.type" should be equal to the string "partner"
    And the JSON node "hydra:member[0].shipTo.code" should be equal to the string "GBT0001"
    And the JSON node "hydra:member[0].shipTo.address" should be equal to "A00000006"
    And the JSON node "hydra:member[0].tracking.carrierTrackingNumber" should exist
    And the JSON node "hydra:member[0].tracking.trackingNumber" should exist
    And the JSON node "hydra:member[0].references.shipmentReference" should exist
    And the JSON node "hydra:member[0].references.customerOrder" should exist
    And the JSON node "hydra:member[0].shippingDocuments.route" should exist
    And the JSON node "hydra:member[0].shippingDocuments.deliveryTerms" should exist
    And the JSON node "hydra:member[0].shippingDocuments.pointOfTitlePassage" should exist
    And the JSON node "hydra:member[0].shippingDocuments.estimatedFreightCosts" should be equal to "0"
    And the JSON node "hydra:member[0].shippingDocuments.estimatedFreightCostsCurrency" should exist

  Scenario: Request a given Shipment always returns a 404
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/shipments/AM0000001"
    Then the response status code should be 404

