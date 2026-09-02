Feature: Test Inventory API

  Scenario: Request a single Inventory without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/inventories/project=;item=7300001"
    Then the response status code should be 401

  Scenario: Inventories should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/inventories/project=;item=7300001"
    Then the response status code should be 403

  Scenario: Request a single Inventory should not be accessible to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/inventories/project=;item=7300001"
    Then the response status code should be 403

  Scenario: Request a single Inventory with ASM user
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/inventories/project=;item=7300001"
    Then the response status code should be 200
    And a "txInventory" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txInventory": {
          "project": "",
          "item": "7300001",
          "priceBook": "SL0000002"
        }
      }
    }
    """
    And a total of 1 request has been sent to ION
    And the JSON node "item" should be equal to "7300001"
    And the JSON node "siteItems" should have 21 elements
    And the JSON node "siteItems[0].site" should be equal to "300"
    And the JSON node "siteItems[0].textItem.textItemLangs[0].name" should be equal to "English"
    And the JSON node "siteItems[5].warehouses" should have 2 elements
    And the JSON node "pictures" should have 2 elements
    And the JSON node "pictures[0].key" should be equal to 1
    And the JSON node "pictures[0].uri" should be equal to "/inventories/7300001/images/1"
    And the JSON node "pictures[0].filename" should be equal to "7300001.jpg"
    And the JSON node "pictures[0].extension" should be equal to "jpg"
    And the JSON node "pictures[0].size" should be equal to 197730
    And the JSON node "pictures[1].filename" should be equal to "7300001_2.jpg"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/inventory/schemas/inventory.json"

  Scenario: Request a single Inventory with ASM user and filter out warehouses not included in enterprise planning
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/inventories/project=;item=7300001?includeInEnterprisePlanning=1"
    Then the response status code should be 200
    And a "txInventory" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txInventory": {
          "project": "",
          "item": "7300001",
          "priceBook": "SL0000002",
          "includeInEnterprisePlanning": 1
        }
      }
    }
    """
    And a total of 1 request has been sent to ION
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/inventory/schemas/inventory.json"

  Scenario: Request a single Inventory with ASM user and filter out warehouses not included in enterprise planning with escape characters in item name
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/inventories/project=;item=136449X3.00?includeInEnterprisePlanning=1"
    Then the response status code should be 200
    And a "txInventory" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txInventory": {
          "project": "",
          "item": "136449X3.00",
          "priceBook": "SL0000002",
          "includeInEnterprisePlanning": 1
        }
      }
    }
    """
    And a total of 1 request has been sent to ION
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/inventory/schemas/inventory.json"