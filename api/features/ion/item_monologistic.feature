Feature: ERP Item can be fetched through the API

  Scenario: Request Item without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/item_monologistics?selection[]=description&selection[]=itemCode&itemCode[like]=%25120%25"
    Then the response status code should be 401

  Scenario: Item should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/item_monologistics?selection[]=description&selection[]=itemCode&itemCode[like]=%25120%25"
    Then the response status code should be 403

  Scenario: Item should not be accessible to vendor user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/item_monologistics?selection[]=description&selection[]=itemCode&itemCode[like]=%25120%25"
    Then the response status code should be 403

  Scenario: Request a list of Items filtered by item code and with field selection
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/item_monologistics?selection[]=description&selection[]=itemCode&itemCode[like]=%25120%25"
    And a "Item_v3" SOAP client has been created
    And this client has been called on the operation "List" with the following request:
    """
     {
        "ControlArea": {
            "Selection": {
                "selectionAttribute": [
                    "Item_v3.description",
                    "Item_v3.itemCode"
                ]
            },
            "maxNumberOfObjects": 500,
            "Filter": {
                "LogicalExpression": {
                    "logicalOperator": "and",
                    "ComparisonExpression": [
                        {
                            "comparisonOperator": "like",
                            "instanceValue": "%120%",
                            "attributeName": "Item_v3.itemCode"
                        }
                    ]
                }
            }
        }
    }
    """
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 500 elements
    And the JSON node "hydra:member[0].itemCode" should be equal to "!TEMP1120"
    And the JSON node "hydra:member[0].description" should be equal to the string "COIL"
    And the JSON node "hydra:member[1].itemCode" should be equal to "!TEMP1200"
    And the JSON node "hydra:member[1].description" should be equal to the string "ELEMENT, OIL FILTER"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/item/schemas/items_monologistic.json"

  Scenario: Request an Item
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/item_monologistics/1048120?selection[]=description&selection[]=itemCode&selection[]=baseUOM"
    And a "Item_v3" SOAP client has been created
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "Item_v3": {
          "itemCode": "         1048120"
        }
      },
      "ControlArea": {
        "Selection": {
           "selectionAttribute": [
              "Item_v3.description",
              "Item_v3.itemCode",
              "Item_v3.baseUOM"
           ]
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "itemCode" should be equal to "1048120"
    And the JSON node "description" should be equal to the string "HARNESS, KIT CRADDLE TPX-500"
    And the JSON node "unitOfMeasure" should be equal to "EA"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/item/schemas/item_monologistic.json"

  Scenario: Request an Item should trigger a not found error
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/item_monologistics/lala123"
    And a "Item_v3" SOAP client has been created
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "Item_v3": {
          "itemCode": "         lala123"
        }
      }
    }
    """
    Then the response status code should be 404
    And the JSON node "hydra:description" should be equal to "Not Found"
