Feature: Test Purchase Order API

  Scenario: Purchase orders should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/purchase_orders"
    Then the response status code should be 403

  Scenario: Request purchase order for vendor user with permissions
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/purchase_orders/V50483773"
    Then the response status code should be 200
    And a "txPurchaseOrder" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txPurchaseOrder": {
          "orderIdentifier": "V50483773",
          "scenario": "ACT",
          "itemCodeSystem": "SUP"
        }
      }
    }
    """
    And the JSON should be valid according to the schema "tests/fixtures/json/purchase_order/schemas/purchase_order.json"
    And a total of 1 request has been sent to ION
    And the JSON node "lines[11].description" should be equal to the string "ENGINE,TD3.6L4 55,4kW ,T4F"


  Scenario: Request purchase order with otherLanguage in fr for vendor user with permissions translate description
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/purchase_orders/V50483773?otherLanguage=fr"
    Then the response status code should be 200
    And a "txPurchaseOrder" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
       "DataArea": {
         "txPurchaseOrder": {
           "orderIdentifier": "V50483773",
           "scenario": "ACT",
           "itemCodeSystem": "SUP",
           "otherLanguage": "fr"
         }
       }
    }
    """
    And the JSON should be valid according to the schema "tests/fixtures/json/purchase_order/schemas/purchase_order.json"
    And a total of 1 request has been sent to ION
    And the JSON node "lines[11].description" should be equal to the string "Moteur &agrave; T&ecirc;te o&ugrave; Carr&eacute; dro&iuml;t"


  Scenario: Comment purchase order for vendor user with permissions
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/ion/purchase_orders/V50483368",
      "message": "test"
    }
    """
    Then the response status code should be 201
    And a "txPurchaseOrder" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txPurchaseOrder": {
          "orderIdentifier": "V50483368",
          "scenario": "ACT",
          "itemCodeSystem": "SUP"
        }
      }
    }
    """
    And a total of 1 request has been sent to ION

  Scenario: Comment purchase order for vendor user with no permissions
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/ion/purchase_orders/CHI000063",
      "message": "test"
    }
    """
    Then the response status code should be 403
    And a "txPurchaseOrder" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txPurchaseOrder": {
          "orderIdentifier": "CHI000063",
          "scenario": "ACT",
          "itemCodeSystem": "SUP"
        }
      }
    }
    """
    And a total of 1 request has been sent to ION

  Scenario: Request purchase order for vendor user with no permissions
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/purchase_orders/CHI000063"
    Then the response status code should be 403

  Scenario: Request all Purchase Orders
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/purchase_orders?buyFromSupplierCode=DEU0016&open=1&orderDate[after]=2000-01-19&orderDate[before]=2030-04-19"
    Then the response status code should be 200
    And a "txPurchaseOrder" SOAP client has been created
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
        "txPurchaseOrder": {
          "scenario": "ACT",
          "itemCodeSystem": "SUP",
          "orderDateAfter": "2000-01-19",
          "orderDateBefore": "2030-04-19",
          "status": "15|20|35",
          "buyFromSupplierCode": "DEU0016"
        }
      }
    }
    """
    And the JSON should be valid according to the schema "tests/fixtures/json/purchase_order/schemas/purchase_orders.json"
    And a total of 1 request has been sent to ION

  Scenario: Request all Purchase Orders lines open
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/purchase_orders?onlyOpenedLines=1&open=1"
    Then the response status code should be 200
    And a "txPurchaseOrder" SOAP client has been created
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
            "txPurchaseOrder": {
                "scenario": "ACT",
                "itemCodeSystem": "SUP",
                "status": "15|20|35",
                "onlyOpenedLines": "1"
            }
        }
    }
    """
  And the JSON should be valid according to the schema "tests/fixtures/json/purchase_order/schemas/purchase_orders.json"
  And the JSON node "hydra:member" should have 58 elements
  And a total of 1 request has been sent to ION

  Scenario: Request a given Purchase Order
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/purchase_orders/EUR000016"
    Then the response status code should be 200
    And a "txPurchaseOrder" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txPurchaseOrder": {
          "orderIdentifier": "EUR000016",
          "scenario": "ACT",
          "itemCodeSystem": "SUP"
        }
      }
    }
    """
    And the JSON should be valid according to the schema "tests/fixtures/json/purchase_order/schemas/purchase_order.json"
    And the JSON node "lines" should have 2 elements
    And the JSON node "lines[0].description" should be equal to the string "FAN BELT, DEUTZ T3 929"
    And the JSON node "lines[1].description" should be equal to the string "SECURITY CARTRIDGE"
    And a total of 1 request has been sent to ION

  Scenario: Download a purchase order drawings Zip
    Given I authenticate as the intranet user "user-basic@tld.fr"
    Given I add "Accept" header equal to "application/zip"
    When I send a "GET" request to "/ion/purchase_orders/EUR000016/zip"
    And a "txPurchaseOrder" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txPurchaseOrder": {
          "orderIdentifier": "EUR000016",
          "scenario": "ACT",
          "itemCodeSystem": "SUP"
        }
      }
    }
    """
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txMultiLevelBom" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "product": "041439-003",
          "site": 540,
          "project": "",
          "depth": 20,
          "date": "2008-06-26T22:00:00Z"
        }
      }
    }
    """
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txMultiLevelBom" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "product": "5033395-009",
          "site": 540,
          "project": "",
          "depth": 20,
          "date": "1989-12-31T23:00:00Z"
        }
      }
    }
    """
    And a total of 3 request has been sent to ION
    Then the response status code should be 204

  Scenario: Update a given Purchase Order and send email and update currentPlannedReceiptDate
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/ion/purchase_orders/CHI000063" with body:
    """
    {
      "lines": [
        {
          "lineIdentifier": "10",
          "sequence": 1,
          "confirmedSupplierDate": "2033-04-19"
        },
        {
          "lineIdentifier": "20",
          "sequence": 1,
          "confirmedSupplierDate": "2043-04-19"
        },
        {
          "lineIdentifier": "30",
          "sequence": 1,
          "confirmedSupplierDate": "2053-04-19"
        }
      ],
      "editMessage": "100 patates!!"
    }
    """
    And a "txPurchaseOrder" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txPurchaseOrder": {
          "orderIdentifier": "CHI000063",
          "scenario": "ACT",
          "itemCodeSystem": "SUP"
        }
      }
    }
    """
    Then a "txPurchaseOrder" SOAP client has been created
    And this client has been called on the operation "txChange" with the following request:
    """
    {
      "DataArea": {
        "txPurchaseOrder": {
          "scenario": "ACT",
          "itemCodeSystem": "SUP",
          "orderIdentifier": "CHI000063",
          "Line": [
            {
              "lineIdentifier": "10",
              "sequence": 1,
              "confirmedSupplierDate": "2033-04-19"

            },
            {
              "lineIdentifier": "20",
              "sequence": 1,
              "confirmedSupplierDate": "2043-04-19"
            },
            {
              "lineIdentifier": "30",
              "sequence": 1,
              "confirmedSupplierDate": "2053-04-19"
            }
          ]
        }
      }
    }
    """
    And a total of 3 requests has been sent to ION
    And the column "module" from the "mod_logs" legacy table has been inserted with string "PO"
    And the column "comment" from the "mod_logs" legacy table has been inserted with string "100 patates!!"
    And the column "parent_id" from the "mod_logs" legacy table has been inserted with integer 0
    And the response status code should be 200
    And a comment should have been inserted on resource "/ion/purchase_orders/CHI000063" with message "100 patates!!" by "user-basic@tld.fr"
    And an email should have been sent asynchronously with subject "Vendor SHA0162 response for PO#CHI000063"
    And this asynchronous email should be sent only to "jie.yang@tld-asia.com"
    And this asynchronous email should contain "100 patates!!"

  Scenario: Update a given Purchase Order with confirmedSupplierDate strictly preceding the current delivery date causes an error
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/ion/purchase_orders/V54925226" with body:
    """
    {
      "lines": [
        {
          "lineIdentifier": "1",
          "sequence": 1,
          "confirmedSupplierDate": "2012-04-18"
        },
        {
          "lineIdentifier": "2",
          "sequence": 1,
          "confirmedSupplierDate": "2010-04-15"
        }
      ],
      "editMessage": "100 patates!!"
    }
    """
    And a "txPurchaseOrder" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txPurchaseOrder": {
          "orderIdentifier": "V54925226",
          "scenario": "ACT",
          "itemCodeSystem": "SUP"
        }
      }
    }
    """
    And a total of 1 request has been sent to ION
    And the response status code should be 422
    # We're comparing to current date so it can't be strictly tested
    And the JSON node "hydra:description" should contain "Please set the delivery date in the future"

  Scenario: Update a given Purchase Order without purchase order lines causes an error
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/ion/purchase_orders/V54925226" with body:
    """
    {
      "lines": [
      ],
      "editMessage": "100 patates!!"
    }
    """
    And a "txPurchaseOrder" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txPurchaseOrder": {
          "orderIdentifier": "V54925226",
          "scenario": "ACT",
          "itemCodeSystem": "SUP"
        }
      }
    }
    """
    And a total of 1 request has been sent to ION
    And the response status code should be 422
    And the JSON node "hydra:description" should contain "lines: This collection should contain 1 element or more."

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Procurement\Orders\PurchaseOrder" is exposed on the API
    Then the filter "buyFromSupplierCode" should be available and its type should be "string"
    Then the filter "open" should be available and its type should be "bool"
    Then the filter "orderDate[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "orderDate[before]" should be available and its type should be "DateTimeInterface"

  Scenario: Request PDF labels
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/pdf"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/ion/purchase_orders/CHI000063/pdf_labels" with body:
    """
    {
      "lines": [
        {
          "lineIdentifier": "10",
          "sequence": 1,
          "quantityLabel": 5,
          "packingSlip": "123"
        }
      ]
    }
    """
    Then the response status code should be 200
    And a total of 1 request has been sent to ION

  Scenario: Can't access request PDF labels for vendor user with purchase order not authorized to read
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/pdf"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/ion/purchase_orders/CHI000063/pdf_labels" with body:
    """
    {
      "lines": [
        {
          "lineIdentifier": "10",
          "sequence": 1,
          "quantityLabel": 5,
          "packingSlip": "123"
        }
      ]
    }
    """
    Then the response status code should be 403

  Scenario: Request PDF labels for vendor user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/pdf"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/ion/purchase_orders/V50483368/pdf_labels" with body:
    """
    {
      "lines": [
        {
          "lineIdentifier": "1",
          "sequence": 1,
          "quantityLabel": 3,
          "packingSlip": "123"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/pdf"
    And a total of 1 request has been sent to ION

  Scenario: Get full purchase orders for excel export should be possible
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/ion/purchase_orders"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | PO# | ERP | Supplier Number | Order Date | Reference A | Reference B | PO Status | Qty of PO lines | Line | Sequence | Part Number | Supplier PN | Line State | Revision Status | Revision | Order Qty | Del Qty | Back Qty | Description | Orig Req. Date | Last Resched Del. Date | Confirmed Del. Date | Line Status | Line Delivery Status |
