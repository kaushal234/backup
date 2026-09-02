Feature: A BOM Materials can be fetched through the API

  Scenario: Request a purchasing BOM without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/purchasing_benchmarks/site=520;project=;product=1200676?otherSites=500|540|570"
    Then the response status code should be 401

  Scenario: Request a not existing purchasing BOM
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/purchasing_benchmarks/site=520;project=;product=1247676666?otherSites=500|540|570"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txBomMultiSite" with the following request:
    """
    {
        "DataArea": {
            "txCustomizedBillOfMaterials_v2": {
                "site": 520,
                "project": "",
                "product": "1247676666",
                "otherSites": "500|540|570"
            }
        }
    }
    """
    Then the response status code should be 404

  Scenario: Request a purchasing BOM
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/bill-of-materials/purchasing_benchmarks/site=520;project=;product=1200676?otherSites=500|540|570"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txBomMultiSite" with the following request:
    """
    {
        "DataArea": {
            "txCustomizedBillOfMaterials_v2": {
                "site": 520,
                "project": "",
                "product": "1200676",
                "otherSites": "500|540|570"
            }
        }
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/bom/schemas/benchmark.json"
    And the JSON node "site" should be equal to the number 520
    And the JSON node "project" should be equal to the string ""
    And the JSON node "product" should be equal to "1200676"
    And the JSON node "itemDescription" should be equal to the string "BATTERY PACK,80V 277AH,iBS"
    And the JSON node "supplySource" should be equal to the string "Purchase"
    And the JSON node "purchasingBySites" should have 4 elements
    And the JSON node "purchasingBySites[0].site" should be equal to the number 500
    And the JSON node "purchasingBySites[0].price" should be equal to the number 29.01
    And the JSON node "purchasingBySites[0].currency" should be equal to the string 'EUR'
    And the JSON node "purchasingBySites[0].buyer" should be equal to '217'
    And the JSON node "purchasingBySites[0].leadTime" should be equal to the number 125
    And the JSON node "purchasingBySites[0].leadTimeUnit" should be equal to '10'
    And the JSON node "purchasingBySites[0].mainSupplier" should be equal to the string 'TEST00001'
    And the JSON node "purchasingBySites[0].standardCost" should be equal to the number 5891.56
    And the JSON node "purchasingBySites[0].costCurrency" should be equal to the string 'EUR'