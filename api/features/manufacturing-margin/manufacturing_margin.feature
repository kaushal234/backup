Feature: Test manufacturing margins

  Scenario: Request all manufacturing margins without being authenticated
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "finance/manufacturing_margins"
    Then the response status code should be 401

  Scenario: Request a single market intelligence without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/manufacturing_margins/1"
    Then the response status code should be 401

  Scenario: Manufacturing margins should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/manufacturing_margins"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Finance\ManufacturingMargin" is exposed on the API
    Then the filter "legacyId" should be available and its type should be "int"
    And the filter "columns" should be available and its type should be "string"

  Scenario: Get full manufacturing margins for export should be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/finance/manufacturing_margins?columns=exportedAt,equipmentRecord.serialNumber,equipmentRecord.product.financeFamily,equipmentRecord.model,equipmentRecord.emissionRating,sol.customerUser,currency,factoryRevenue,factoryDiscount,actualDirectMarginPercentage,estDirMarginPer,modelBaseHours,industrialIncorporationParameter,optionConfigurationParameterHours,unitAllocatedHours,actualHours,factoryStandardEfficiency,solId,equipmentRecord.manufacturerLocation,sso,equipmentRecord.type,standardDirectMarginPercentage,standardMaterialCost,actualMaterialCost,standardOtherDirectCost,actualOtherDirectCost,targetHours,standardHours,factoryStandardEfficiencyBudget"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Date | Equipment Record | Finance Family | Model | Emission Rating | Customer User | Currency | Tp | Factory Discount | Act Dm (%) | Proj Dm (%) | Mbh | Iip (%) | Ocp Hours | Uah | Actual Hours | Fse Actual | Sol Id | Factory | Sso | Product Type | Std Dm (%) | Standard Material Cost | Actual Material Cost | Standard Other Direct Cost | Actual Other Direct Cost | Target Hours | Standard Hours | Fse Budget |

  Scenario: Get manufacturing margins for export should be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/finance/manufacturing_margins?columns=exportedAt,equipmentRecord.serialNumber,equipmentRecord.product.financeFamily,equipmentRecord.model,equipmentRecord.emissionRating,sol.customerUser,currency,factoryRevenue,factoryDiscount,actualDirectMarginPercentage,estDirMarginPer,modelBaseHours,industrialIncorporationParameter,optionConfigurationParameterHours,unitAllocatedHours,actualHours,factoryStandardEfficiency"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Date | Equipment Record | Finance Family | Model | Emission Rating | Customer User | Currency | Tp | Factory Discount | Act Dm (%) | Proj Dm (%) | Mbh | Iip (%) | Ocp Hours | Uah | Actual Hours | Fse Actual |

  Scenario: Get manufacturing margins report should be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/finance/manufacturing_margin_syntheses?factory=/locations/7&exportDate[after]=2016-01-01&exportDate[before]=2017-01-31"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Date | Finance Family | Product Type | Qty | Cur | Av. Tp | Av. Discount | Av. Act. Dm (%) | Av. Proj. Dm (%) | Av. Mbh | Av. Iip (%) | Av. Ocp Hours | Av. Uah | Av. Act. Hours | Fse Actual |

  Scenario: Request all manufacturing margins as user cfo should be possible
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/manufacturing_margins"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manufacturing_margin/schemas/manufacturing_margins.json"
    And the JSON node "hydra:totalItems" should be equal to 3

  Scenario: Request all manufacturing margins as user cmo should be possible
    Given I authenticate as the intranet user "user-cmo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/manufacturing_margins"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manufacturing_margin/schemas/manufacturing_margins.json"
    And the JSON node "hydra:totalItems" should be equal to 3

  Scenario: Request all manufacturing margins as basic user should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/manufacturing_margins"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 0

  Scenario: Request one manufacturing margins user cfo should be possible
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/manufacturing_margins/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manufacturing_margin/schemas/manufacturing_margin.json"

  Scenario: Request one manufacturing margins fo user FC, MPE or PS should be possible if he has acl on location of ER
    Given I authenticate as the intranet user "user-fc@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/manufacturing_margins/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manufacturing_margin/schemas/manufacturing_margin.json"
    Given I authenticate as the intranet user "user-mpe@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/manufacturing_margins/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manufacturing_margin/schemas/manufacturing_margin.json"
    Given I authenticate as the intranet user "user-pm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/manufacturing_margins/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manufacturing_margin/schemas/manufacturing_margin.json"

  Scenario: Request one manufacturing margins for user FC, MPE, PS should not be possible if he hasn't acl on location of ER
    Given I authenticate as the intranet user "user-mpe@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/manufacturing_margins/3"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-ps@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/manufacturing_margins/3"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-fc@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/manufacturing_margins/3"
    Then the response status code should be 403

  Scenario: Request one manufacturing margins as basic user should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/manufacturing_margins/1"
    Then the response status code should be 403

  Scenario: Create a manufacturing margin as a user cfo should be possible
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/finance/manufacturing_margins" with the body "tests/fixtures/json/manufacturing_margin/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/manufacturing_margin/schemas/manufacturing_margin.json"

  Scenario: Create a manufacturing margin with already existing ER line should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/finance/manufacturing_margins" with the body "tests/fixtures/json/manufacturing_margin/dummies/post.json"
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "Record for ER (id: 14, legacyId: 37468, sn: BRIAN) already exists"
    And the JSON node "violations[0].propertyPath" should be equal to the string "equipmentRecord"

  Scenario: Create a manufacturing margin as a user basic should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/finance/manufacturing_margins" with the body "tests/fixtures/json/manufacturing_margin/dummies/post.json"
    Then the response status code should be 403

  Scenario: Update a manufacturing margin as a user FC of the manufacturer of the ER should be possible
    Given I authenticate as the intranet user "user-fc@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/finance/manufacturing_margins/1" with body:
    """
    {
      "standardLabourCost": 69
    }
    """
    Then the response status code should be 200

  Scenario: Update a manufacturing margin as a user cfo should be possible
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/finance/manufacturing_margins/1" with the body "tests/fixtures/json/manufacturing_margin/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/manufacturing_margin/schemas/manufacturing_margin.json"

  Scenario: Update a manufacturing margin as a user MPE or PS should not be possible
    Given I authenticate as the intranet user "user-mpe@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/finance/manufacturing_margins/2" with body:
    """
    {
      "standardLabourCost": 69
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-ps@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/finance/manufacturing_margins/2" with body:
    """
    {
      "standardLabourCost": 69
    }
    """
    Then the response status code should be 403


  Scenario: Delete a Product manufacturing should not be possible for basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/finance/manufacturing_margins/1"
    Then the response status code should be 403

  Scenario: Delete a Product manufacturing should be possible for superuser
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/finance/manufacturing_margins/1"
    Then the response status code should be 204

  Scenario: Import manufacturing margins as batch as a user basic should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "POST" request to "/finance/manufacturing_margins/import_file" with file "file" "file_margins.xlsx"
    Then the response status code should be 403

  Scenario: Create a manufacturing margin as batch as a user cfo should be possible
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "POST" request to "/finance/manufacturing_margins/import_file" with file "file" "file_margins.xlsx"
    Then the response status code should be 204
    And an email should have been sent asynchronously with subject matching pattern "/^Factory Margin Variance Report, location_factory, 2019-09$/"
    And this asynchronous email should be sent only to "user-psm@tld.fr, user-psa@tld.fr, user-pse@tld.fr, user-ceo@tld.fr, user-rceo@tld.fr"

  Scenario: Create a manufacturing margin as batch with already existing ER line should return error
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "POST" request to "/finance/manufacturing_margins/import_file" with file "file" "file_margins.xlsx"
    Then the response status code should be 422
    And no email should have been sent asynchronously
    And the JSON node "violations[0].propertyPath" should be equal to the string "margins[0].equipmentRecord"
    And the JSON node "violations[0].message" should be equal to the string "Record for ER (id: 15, legacyId: 37469, sn: THOMAS) already exists"
