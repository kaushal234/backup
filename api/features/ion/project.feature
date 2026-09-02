Feature: ERP Projects can be fetched through the API

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Manufacturing\Project\Project" is exposed on the API
    Then the filter "productionOrder" should be available and its type should be "string"

  Scenario: Request Projects without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/projects/site=250;project=T83010"
    Then the response status code should be 401

  Scenario: Projects should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/projects/site=250;project=T83010"
    Then the response status code should be 403

  Scenario: Request a single Project
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/projects/site=250;project=T83010"
    And a "txProject" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
          "txProject": {
              "site": "250",
              "project": "T83010"
          }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "@id" should be equal to the string "/ion/projects/site=250;projectIdentifier=T83010"
    And the JSON node "@type" should be equal to the string "Project"
    And the JSON node "site" should be equal to "250"
    And the JSON node "projectIdentifier" should be equal to "T83010"
    And the JSON node "status" should be equal to the string "active"
    And one JSON array element at node productionOrders should contain "WOU000481" in property "productionOrderIdentifier"
    And the JSON node "productionOrders[0].operations[0].reference" should be equal to "3"
    And the JSON node "productionOrders[0].status" should be equal to the string "completed"
    And the JSON node "productionOrders" should have 2 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/project/schemas/item.json"

  Scenario: Request a single Project with a specific production order
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/projects/site=250;project=T83010?productionOrder=WOU000481"
    And a "txProject" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
          "txProject": {
              "site": "250",
              "project": "T83010",
              "productionOrder": "WOU000481"
          }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "@id" should be equal to the string "/ion/projects/site=250;projectIdentifier=T83010"
    And the JSON node "@type" should be equal to the string "Project"
    And the JSON node "site" should be equal to "250"
    And the JSON node "status" should be equal to the string "active"
    And the JSON node "productionOrders[0].status" should be equal to the string "completed"
    And the JSON node "productionOrders[0].productionOrderIdentifier" should be equal to the string "WOU000481"
    And the JSON node "productionOrders" should have 1 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/project/schemas/item.json"

  Scenario: Request a single Project with a specific production order for chinese
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/projects/site=640;project=T85301?languageID=zh&productionOrder=WOU000482"
    And a "txProject" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
          "txProject": {
              "site": "640",
              "project": "T85301",
              "productionOrder": "WOU000482",
              "languageID": "zh"
          }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "@id" should be equal to the string "/ion/projects/site=640;projectIdentifier=T85301"
    And the JSON node "@type" should be equal to the string "Project"
    And the JSON node "site" should be equal to "640"
    And the JSON node "productionOrders[0].operations[0].referenceDescription" should be equal to the string "准备，动力总成"
