Feature: Test Engineering Item API

  Scenario: Request Engineering Item without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/engineering_items/item=1147838;project="
    Then the response status code should be 401
    When I send a "GET" request to "/ion/engineering_item_descriptions?itemsList=ZELBP79|ZZ90061"
    Then the response status code should be 401

  Scenario: Engineering Item should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/engineering_items/item=1147838;project="
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/engineering_item_descriptions?itemsList=ZELBP79|ZZ90061"
    Then the response status code should be 403

  Scenario: Request revisions list of Engineering Item
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/engineering_items/item=1147838;project="
    And a "txEngineeringItem" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txEngineeringItem": {
          "item": "1147838",
          "project": ""
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "@id" should be equal to the string "/ion/engineering_items/item=1147838;project="
    And the JSON node "@type" should be equal to the string "EngineeringItem"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/engineering_item/schemas/item.json"
    And the JSON node "item" should be equal to "1147838"
    And the JSON node "signalCode" should be equal to the string "IGZ"
    And the JSON node "revisions" should have 4 elements
    And the JSON node "revisions[0].revision" should be equal to "A"
    And the JSON node "revisions[1].revision" should be equal to "B"
    And the JSON node "revisions[2].revision" should be equal to "C"
    And the JSON node "revisions[3].revision" should be equal to "X"

  Scenario: Request description list of Engineering Item
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/engineering_item_descriptions?itemsList=ZELBP79|ZZ90061"
    And a "txEngineeringItem" SOAP client has been created
    And this client has been called on the operation "txDescriptionList" with the following request:
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
        "txEngineeringItem": {
          "itemsList": "ZELBP79|ZZ90061"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "@id" should be equal to the string "/ion/engineering_item_descriptions"
    And the JSON node "hydra:member" should have 2 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/engineering_item/schemas/item_description.json"
    And the JSON node "hydra:member[0].item" should be equal to the string "ZELBP79"
    And the JSON node "hydra:member[0].description" should be equal to the string "BULB"
    And the JSON node "hydra:member[1].item" should be equal to the string "ZZ90061"
    And the JSON node "hydra:member[1].description" should be equal to the string "SHAFT"