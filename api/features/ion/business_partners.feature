Feature: ERP Business Partners can be fetched through the API

  Scenario: Request a single Business Partner without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/business_partners?itemsPerPage=10"
    Then the response status code should be 401
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/business_partners/AMA0010"
    Then the response status code should be 401

  Scenario: Business Partners should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/business_partners?itemsPerPage=10"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/business_partners/AMA0010"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\MasterData\BusinessPartners\BusinessPartner" is exposed on the API
    Then the filter "logicalOperator" should be available and its type should be "string"
    Then the ION filter "name" should be available and its type should be "string"
    Then the ION filter "code" should be available and its type should be "string"
    Then the filter "role" should be available and its type should be "string"
    Then the filter "q" should be available and its type should be "string"

  Scenario: Request a collection of Business Partners
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/business_partners?role=supplier&name[like][]=DANA%&name[like][]=%ITALIA%&name[like][]=%SPA%"
    And a "txBusinessPartner" SOAP client has been created
    And this client has been called on the operation "List" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 500,
        "Filter": {
          "LogicalExpression": {
            "logicalOperator": "and",
            "ComparisonExpression": [
              {
                "comparisonOperator": "like",
                "instanceValue": "DANA%",
                "attributeName": "txBusinessPartner.name"
              },
              {
                "comparisonOperator": "like",
                "instanceValue": "%ITALIA%",
                "attributeName": "txBusinessPartner.name"
              },
              {
                "comparisonOperator": "like",
                "instanceValue": "%SPA%",
                "attributeName": "txBusinessPartner.name"
              }
            ],
            "LogicalExpression": [
              {
                "logicalOperator": "or",
                "ComparisonExpression": [
                  {
                    "comparisonOperator": "eq",
                    "instanceValue": "supplier",
                    "attributeName": "txBusinessPartner.role"
                  },
                  {
                    "comparisonOperator": "eq",
                    "instanceValue": "both",
                    "attributeName": "txBusinessPartner.role"
                  }
                ]
              }
            ]
          }
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "hydra:member[0].@id" should be equal to the string "/ion/business_partners/F50H07"
    And the JSON node "hydra:member[0].code" should be equal to the string "F50H07"
    And the JSON node "hydra:member[0].name" should be equal to the string "DANA ITALIA SPA"
    And the JSON node "hydra:member[0].status" should be equal to the string "active"
    And the JSON node "hydra:member" should have 1 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/business_partners/schemas/business_partners.json"

  Scenario: Search business partners by their name or code with all cases and accents
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/business_partners?q=abcdefghijklmnopqrstuvw0123456789ÀâÇçñ&role=supplier"
    And a "txBusinessPartner" SOAP client has been created
    And this client has been called on the operation "List" with the following request:
      """
      {
        "ControlArea": {
          "maxNumberOfObjects": 500,
          "Filter": {
            "LogicalExpression": {
              "logicalOperator": "and",
              "LogicalExpression": [
                {
                  "logicalOperator": "or",
                  "ComparisonExpression": [
                    {
                      "comparisonOperator": "eq",
                      "instanceValue": "supplier",
                      "attributeName": "txBusinessPartner.role"
                    },
                    {
                      "comparisonOperator": "eq",
                      "instanceValue": "both",
                      "attributeName": "txBusinessPartner.role"
                    }
                  ]
                },
                {
                  "logicalOperator": "or",
                  "ComparisonExpression": [
                    {
                      "comparisonOperator": "like",
                      "instanceValue": "%[aA][bB][cC][dD][eE][fF][gG][hH][iI][jJ][kK][lL][mM][nN][oO][pP][qQ][rR][sS][tT][uU][vV][wW]0123456789[ÀàAa][âÂaA][ÇçCc][çÇcC][ñÑnN]%",
                      "attributeName": "txBusinessPartner.name"
                    },
                    {
                      "comparisonOperator": "like",
                      "instanceValue": "%[aA][bB][cC][dD][eE][fF][gG][hH][iI][jJ][kK][lL][mM][nN][oO][pP][qQ][rR][sS][tT][uU][vV][wW]0123456789[ÀàAa][âÂaA][ÇçCc][çÇcC][ñÑnN]%",
                      "attributeName": "txBusinessPartner.code"
                    }
                  ]
                }
              ]
            }
          }
        }
      }
      """
    Then the response status code should be 200

  Scenario: Request a collection of Business Partners limited to one item
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/business_partners?itemsPerPage=1&name[like]=AMAZON%"
    And a "txBusinessPartner" SOAP client has been created
    And this client has been called on the operation "List" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 1,
          "Filter": {
            "LogicalExpression": {
              "logicalOperator": "and",
                "ComparisonExpression": [
                  {
                    "comparisonOperator": "like",
                    "instanceValue": "AMAZON%",
                    "attributeName": "txBusinessPartner.name"
                  }
                ]
            }
          }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 1 element
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/business_partners/schemas/business_partners.json"

  Scenario: Request a single Business Partner
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/business_partners/F50H07"
    And a "txBusinessPartner" SOAP client has been created
    And this client has been called on the operation "txShow" with the following request:
    """
    {
      "DataArea": {
        "txBusinessPartner": {
          "code": "F50H07"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "code" should be equal to "F50H07"
    And the JSON node "name" should be equal to the string "DANA ITALIA SPA"
    And the JSON node "status" should be equal to the string "active"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/business_partners/schemas/business_partner.json"
