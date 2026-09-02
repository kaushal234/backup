Feature: Carriers can be fetched through the API

  Scenario: Request a single Carrier without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/carriers?itemsPerPage=10"
    Then the response status code should be 401
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/carriers/A01"
    Then the response status code should be 401

  Scenario: Carriers should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/carriers?itemsPerPage=10"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/carriers/C02"
    Then the response status code should be 403

  Scenario: Request a collection of Carriers
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/carriers"
    And a "txCarriersLSP" SOAP client has been created
    And this client has been called on the operation "List" with the following request:
    """
    {
       "ControlArea": {
           "maxNumberOfObjects": 500,
           "Filter": {
               "LogicalExpression": {
                   "logicalOperator": "and"
               }
           }
       }
    }
    """
    Then the response status code should be 200
    And the JSON node "hydra:member[0].@id" should be equal to the string "/ion/carriers/A01"
    And the JSON node "hydra:member[0].name" should be equal to the string "Air Parcel"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/carrier/schemas/collection.json"

  Scenario: Request a single Carrier
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/carriers/C02"
    And a "txCarriersLSP" SOAP client has been created
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "txCarriersLSP": {
          "code": "C02"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "code" should be equal to the string "C02"
    And the JSON node "name" should be equal to the string "Clasquin"
    And the JSON node "buyFromBusinessPartner.@id" should be equal to the string "/ion/business_partners/CLA0011"
    And the JSON node "buyFromBusinessPartner.@type" should be equal to the string "BusinessPartner"
    And the JSON node "buyFromBusinessPartner.code" should be equal to "CLA0011"
    And the JSON node "buyFromBusinessPartner.name" should be equal to the string "CLASQUIN FRANCE SA"
    And the JSON node "buyFromBusinessPartner.role" should be equal to the string "supplier"
    And the JSON node "buyFromBusinessPartner.status" should be equal to the string "active"
    And the JSON node "buyFromBusinessPartner.text" should be equal to the string ""
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/carrier/schemas/item.json"
