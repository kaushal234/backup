Feature: Test Production order API

  Scenario: Update production order is not allowed for basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/ion/production_orders" with body:
    """
    {
      "site": 570,
      "project": [
        {
          "code": "T75693",
          "openCrabs": 2,
          "totalCrabs": 4
        }
      ]
    }
    """
    Then the response status code should be 403

  Scenario: Update production order for superuser with permissions
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/ion/production_orders" with body:
    """
    {
      "site": 570,
      "project": [
        {
          "code": "T75693",
          "openCrabs": 2,
          "totalCrabs": 4
        }
      ]
    }
    """
    Then the response status code should be 201
    And a "txProductionOrder" SOAP client has been created
    And this client has been called on the operation "txUpdateCrabs" with the following request:
    """
    {
      "DataArea": {
        "txProductionOrder": {
          "site": 570,
          "project": [
            {
                "code": "T75693",
                "openCrabs": 2,
                "totalCrabs": 4
            }
          ]
        }
      }
    }
    """
    And a total of 1 request has been sent to ION
    And the JSON node "@type" should be equal to the string "ProductionOrder"
    And the JSON node "project[0].status" should be equal to the string "updated"
    And the JSON node "project[0].openCrabs" should be equal to "2"
    And the JSON node "project[0].totalCrabs" should be equal to "4"
    And the JSON node "site" should be equal to "570"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/production_order/schemas/production_order.json"