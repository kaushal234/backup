Feature: A CBOM Materials can be fetched through the API

  Scenario: Request a single CBOM without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/customized-bill-of-materials/variants/site=220;project=T80651"
    Then the response status code should be 401

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\Variant" is exposed on the API
    Then the filter "date" should be available and its type should be "string"
    Then the filter "otherLanguage" should be available and its type should be "string"

  Scenario: Request a single CBOM
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/customized-bill-of-materials/variants/site=220;project=T80651?normalizationGroups[]=variant"
    And a "txCustomizedBillOfMaterials_v2" SOAP client has been created
    And this client has been called on the operation "txCBOMVariant" with the following request:
    """
    {
      "DataArea": {
        "txCustomizedBillOfMaterials_v2": {
          "site": 220,
          "project": "T80651"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "site" should be equal to the number 220
    And the JSON node "project" should be equal to the string "T80651"
    And the JSON node "productVariants" should have 1 elements
    And the JSON node "productVariants[0].productVariant" should be equal to "3"
    And the JSON node "productVariants[0].description" should be equal to the string "AS SSO (For Mig and First Go-L"
    And the JSON node "productVariants[0].item" should be equal to the string "TRU-28"
    And the JSON node "productVariants[0].referenceOrder" should be equal to the string "T80651"
    And the JSON node "productVariants[0].options" should have 4 elements
    And the JSON node "productVariants[0].options[0].sequenceNumber" should be equal to "10"
    And the JSON node "productVariants[0].options[0].productFeature" should be equal to the string "MTYPE"
    And the JSON node "productVariants[0].options[0].description" should be equal to the string "Mounting Type"
    And the JSON node "productVariants[0].options[0].option" should be equal to the string "C-W"
    And the JSON node "productVariants[0].options[0].optionDescriptionByProductFeature" should be equal to the string "CASTER WHEELS"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/cbom/schemas/variants.json"