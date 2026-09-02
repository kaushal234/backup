Feature: Test Report generation
  Scenario: Get a report for quotations
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/parts/quotations;x=sph.name;y=status"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Reports should not be accessible to extranet user
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/parts/quotations;x=sph.name;y=status"
    Then the response status code should be 403

  Scenario: Get a report for quotations in CSV
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "text/csv"
    When I send a "GET" request to "/reports/resource=/parts/quotations;x=sph.name;y=status"
    Then the response status code should be 200
    And the csv file headers are:
      | | LOST| ORDERED_FULL | PENDING | SUBMITTED_FULL | SUBMITTED_FULL_REVISED | SUBMITTED_PARTIAL | SUSPENDED  |  Totals |
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "text/csv"
    When I send a "GET" request to "/reports/resource=/parts/quotations;x=sph.name;y=status?options[headers][columns][]=PENDING&options[headers][columns][]=LOST&options[hideTotals]=both"
    Then the response status code should be 200
    And the csv file headers are:
      | | PENDING| LOST |

  Scenario: Get a report with an invalid resource
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/scrogneugneu;x=sph.name;y=status"
    Then the response status code should be 422

  Scenario: Get a report without mandatory arguments
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/parts/quotations;x=sph.name"
    Then the response status code should be 404

  Scenario: Get a report with an invalid property
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/parts/quotations;x=sph.pouetpouet;y=status"
    Then the response status code should be 400

  Scenario: Get a report for quotations with filters
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/parts/quotations;x=sph.name;y=status?options[createdAt-after]=2017-07-1&options[createdAt-before]=2050-07-1&options[quoter]=5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for first article qualification with plan status & factory filters
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/quality/first_article_qualifications;x=location.name;y=planApprovalStatus"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for SFR by sso and factory
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=sso.name;y=factory.name?options[asm]=/people/31&options[delinquent]=1"
    Then the response status code should be 200
    And the JSON node "metadata.xIris.location_sso" should be equal to the string "/locations/23"
    And the JSON node "metadata.yIris.location_factory" should be equal to the string "/locations/29"
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for military SFR by sso and factory
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=sso.name;y=factory.name?options[military]=1"
    Then the response status code should be 200

  Scenario: Get a report for SFR by customer and factory
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=buyer.name;y=factory.name?options[asm]=/people/11"
    Then the response status code should be 200
    And the JSON node "metadata.xIris.customer_2" should be equal to the string "/sales/customers/2"
    And the JSON node "metadata.yIris.location_factory" should be equal to the string "/locations/29"
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for military SFR by customer and factory
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=buyer.name;y=factory.name?options[military]=1"
    Then the response status code should be 200

  Scenario: Get a report for SFR by sso and factory for hot deals
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=sso.name;y=factory.name?options[asm]=/people/11&options[hot_deals]=1"
    Then the response status code should be 200
    And the JSON node "metadata.xIris.location_sso" should be equal to the string "/locations/23"
    And the JSON node "metadata.yIris.location_factory" should be equal to the string "/locations/29"
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for SFR by sso and factory for recently ordered
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=sso.name;y=factory.name?options[asm]=/people/47&options[recently]=ordered"
    Then the response status code should be 200
    And the JSON node "metadata.xIris.location_sso" should be equal to the string "/locations/23"
    And the JSON node "metadata.yIris.location_factory" should be equal to the string "/locations/29"
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for SFR by sso and factory for recently lost
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=sso.name;y=factory.name?options[asm]=/people/47&options[recently]=lost"
    Then the response status code should be 200
    And the JSON node "metadata.xIris.location_sso" should be equal to the string "/locations/23"
    And the JSON node "metadata.yIris.location_factory" should be equal to the string "/locations/29"
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for SFR by my asm and factory
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=asm.id;y=factory.name"
    Then the response status code should be 200
    And the JSON node "metadata.xIris.user BASIC" should be equal to the string "/people/11"
    And the JSON node "metadata.yIris.location_factory" should be equal to the string "/locations/29"
    And the JSON node "metadata.xIris.Anne Sophie MARTIN" should exist
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for SFR by my asm and factory
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=asm.id;y=factory.name?options[asm]=/people/31"
    Then the response status code should be 200
    And the JSON node "metadata.xIris.Anne Sophie MARTIN" should be equal to the string "/people/31"
    And the JSON node "metadata.xIris.user BASIC" should not exist
    And the JSON node "metadata.yIris.location_factory" should be equal to the string "/locations/29"
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for SFR top ten customers
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=top_ten;y=value?options[asm]=/people/31"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a SFR valorization report for next 3 months
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=date;y=value?options[asm]=/people/31"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a SFR valorization report for another user
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=date;y=value?options[asm]=/people/32"
    Then the response status code should be 403

  Scenario: Get a SFR valorization report for a SSO
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=date;y=value?options[sso]=/locations/23"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a SFR valorization report for a SSO when not authorized
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=date;y=value?options[sso]=/locations/23"
    Then the response status code should be 403

  Scenario: Get a report for MIM by BU
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/market_intelligences;x=date;y=locations"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for number of ER by customers
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/equipment_records;x=buyer.name;y=units"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/equipment_records;x=buyer.name;y=units"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/equipment_records;x=endUser.name;y=units"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/equipment_records;x=buyer.name;y=product.name"
    Then the response should be an error stating "There's not enough filters to generate this report."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/equipment_records;x=buyer.name;y=product.name?options[product.family.productType]=/sales/product_types/1"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/equipment_records;x=buyer.name;y=product.name?options[product.family.productType]=/sales/product_types/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/equipment_records;x=endUser.name;y=product.name?options[product.family.productType]=/sales/product_types/1&options[product.hidden]=false"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get monetized SFR report by customer
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=customer;y=sso"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "metadata.xIris.customer_14" should be equal to the string "/sales/customers/14"
    And the JSON node "metadata.yIris.location_sso" should be equal to the string "/locations/23"
    And the JSON node "xTotals.customer_14" should be equal to the number 3147
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=customer;y=sso?options[ponderated]=1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "xTotals.customer_14" should be equal to the number 2266
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=customer;y=sso?options[currency]=/finance/currencies/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "xTotals.customer_14" should be equal to the number 3933

  Scenario: Get monetized SFR report by week
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=week;y=sso?options[currency]=/finance/currencies/1&options[ponderated]=0&options[saleDateDistance]=90&options[products][0]=/sales/products/31&options[productTypes][0]=/sales/product_types/2&options[ssos][0]=/locations/30&options[factories][0]=/locations/29"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get quantity SFR report by week
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=week;y=quantity_per_sso?options[saleDateDistance]=90&options[products][0]=/sales/products/31&options[productTypes][0]=/sales/product_types/2&options[ssos][0]=/locations/30&options[factories][0]=/locations/29"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

#  Scenario: Get a report with metadata
#    Given I authenticate as the intranet user "user-superuser@tld.fr"
#    And I add "Accept" header equal to "application/ld+json"
#    When I send a "GET" request to "/reports/resource=/erp/suppliers;x=date;y=SDP?options[erp]=500&options[supplier]=ET0001&options[from]=2019-11-01&options[until]=2019-11-30"
#    Then the response status code should be 200
#    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
#    And the JSON node "metadata.ET0001.name" should be equal to the string "ETA"
#    And the JSON node "metadata.defaultCurrency" should be equal to the string "EUR"

#  Scenario: Get a report with metadata in CSV
#    Then I authenticate as the intranet user "user-superuser@tld.fr"
#    And I add "Accept" header equal to "text/csv"
#    When I send a "GET" request to "/reports/resource=/erp/suppliers;x=date;y=SDP?options[erp]=500&options[supplier]=ET0001&options[from]=2019-11-01&options[until]=2019-11-30&options[headers][columns][]=name&options[headers][columns][]=2019-11&options[hideTotals]=both"
#    Then the response status code should be 200
#    And the csv file headers are:
#      |  | name | 2019-11 |
#    And the csv cell B2 should be equal to "ETA"

  Scenario: Get a SFR with high chance of sale report for a product by factory
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/sales_forecasts;x=product.family.name;y=factory.name?options[hot]=true&options[quantity]=true"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get employee staffing module report
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/people;x=businessUnit.positionClassifications.positionCategory.name;y=contractType.name?options[entity]=/divisions/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "metadata.scope" should be equal to the string "Division"
    And the JSON node "xTotals.Cat & gory" should be equal to the number 2
    And the JSON node "yTotals.Sous-Fifre" should be equal to the number 2
    And the JSON node "total" should be equal to the number 2
    And the JSON node "rows.Cat & gory.Sous-Fifre.value" should be equal to the number 2
    And the JSON node "metadata.classifications.Cat & gory.correction" should be equal to "-3"
    And the JSON node "metadata.classifications.Cat & gory.iris" should have 1 elements
    And the JSON node "metadata.classifications.Cat & gory.iris[0]" should be equal to the string "/position_classifications/2"
    And the JSON node "metadata.classifications.Cat & gory.comments" should have 1 element
    And the JSON node "metadata.classifications.Cat & gory.comments[0]" should be equal to the string "(-3 for business_unit_2) Roger is very very sick"
    And the JSON node "metadata.classifications.Cat & gory.budget" should be equal to the number 30
    And the JSON node "metadata.classifications.Cat & gory.reforecast" should be equal to the number 40
    And the JSON node "metadata.uncategorized[2].count" should exist
    And the JSON node "metadata.uncategorized[2].name" should exist
    And the JSON node "metadata.businessUnits" should have 21 elements
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/people;x=businessUnit.positionClassifications.positionCategory.name;y=contractType.name?options[entity]=/sub_divisions/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "metadata.scope" should be equal to the string "SubDivision"
    And the JSON node "metadata.businessUnits" should have 1 element
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/people;x=businessUnit.positionClassifications.positionCategory.name;y=contractType.name?options[entity]=/regions/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "metadata.scope" should be equal to the string "Region"
    And the JSON node "metadata.businessUnits" should have 13 elements
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/people;x=businessUnit.positionClassifications.positionCategory.name;y=contractType.name?options[entity]=/business_units/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "metadata.scope" should be equal to the string "BusinessUnit"
    And the JSON node "metadata.businessUnits" should have 1 element
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/people;x=businessUnit.positionClassifications.positionCategory.name;y=contractType.name"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "metadata.scope" should be null
    And the JSON node "metadata.businessUnits" should have 24 elements

  Scenario: Get employee staffing module report snapshot
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/people;x=businessUnit.positionClassifications.positionCategory.name;y=contractType.name?options[entity]=/sub_divisions/1&options[snapshotDate]=2021-02-24"
    Then the response status code should be 200
    And the JSON node "@id" should be equal to the string "/reports/resource=/people;x=businessUnit.positionClassifications.positionCategory.name;y=contractType.name"
    And the JSON node "metadata.classifications.Cat & gory.comments[0]" should be equal to the string "(+1 for SAY_MY_NAME) Bob is working like a dog - specific comment to make sure that snapshot data is loaded"
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "createdAt" should exist

  Scenario: Get a report of all the report snapshots
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/reports/id;x=businessUnit;y=createdAt?options[resource]=/people&options[x]=businessUnit.positionClassifications.positionCategory.name&options[y]=contractType.name&options[options][entity]=/sub_divisions/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "yTotals.2021-02-24" should be equal to the number 1

  Scenario: Get a report for people by premise and business Unit
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/people;x=premise.name;y=businessUnit.name?options[type]=22"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "xTotals.Andalouza" should be equal to the number 182

  Scenario: Get SPR reports
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "reports/resource=/parts/spare_parts_requests;x=sph.name;y=status?options[warranty]=false"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "metadata.xIris" should exist

  Scenario: Get a report for supplier corrective action request by status and factory
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "reports/resource=/quality/supplier_corrective_action_requests;x=factory.name;y=status"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for NCR by location and status
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/quality/non_conformities;x=location.name;y=status"
    Then the response status code should be 200
    And the JSON node "metadata.xIris.location_factory" should be equal to the string "/locations/29"
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for NCR for top 10 customer with most NCR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/quality/non_conformities;x=top_ten;y=supplier?options[location]=/locations/11&options[from]=2009-12-19&options[to]=2011-12-19"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for NCR by process for the past last year
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/quality/non_conformities;x=ncr_by_process;y=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for NCR by responsible for the past last year
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/quality/non_conformities;x=ncr_by_responsible;y=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for NCR closed and opened for the past last year
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/quality/non_conformities;x=ncr_opened;y=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for VWC for top part failure
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=top_part_failure;y=factory?options[supplierNumber]=50090&options[factory]=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for VWC for top part failure as extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=top_part_failure;y=factory?options[supplierNumber]=50090&options[factory]=/locations/11"
    Then the response status code should be 403

  Scenario: Get a report for VWC for top part failure as vendor user with no access on supplier
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=top_part_failure;y=factory?options[supplierNumber]=50090&options[factory]=/locations/11"
    Then the response status code should be 403

  Scenario: Get a report for VWC for top part failure as vendor user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=top_part_failure;y=factory?options[supplierNumber]=DAN0013&options[factory]=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for VWC for the history of part failure
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=part_failure_history;y=created_at?options[supplierNumber]=50090&options[factory]=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for VWC for the history of part failure as extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=part_failure_history;y=created_at?options[supplierNumber]=50090&options[factory]=/locations/11"
    Then the response status code should be 403

  Scenario: Get a report for VWC for the history of part failure as vendor user not authorized for supplier
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=part_failure_history;y=created_at?options[supplierNumber]=50090&options[factory]=/locations/11"
    Then the response status code should be 403

  Scenario: Get a report for VWC for the history of part failure as vendor user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=part_failure_history;y=created_at?options[supplierNumber]=DAN0013&options[factory]=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for VWC for the history of supplier
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=supplier_history;y=created_at?options[supplierNumber]=50090&options[factory]=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for VWC for the history of supplier as vendor user with no access on supplier
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=supplier_history;y=created_at?options[supplierNumber]=50090&options[factory]=/locations/11"
    Then the response status code should be 403

  Scenario: Get a report for VWC for the history of supplier as vendor user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=supplier_history;y=created_at?options[supplierNumber]=DAN0013&options[factory]=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for VWC for the history of supplier as extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=supplier_history;y=created_at?options[supplierNumber]=50090&options[factory]=/locations/11"
    Then the response status code should be 403

  Scenario: Get a report for VWC closed by buyers
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=buyer_vwc_closed;y=created_at?options[location]=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for average resolved time in day of VWC closed by buyers
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=resolved_time;y=buyer?options[location]=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for VWC for the history of buyer
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=buyer_history;y=created_at?options[buyer]=/people/21"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for VWC for the supplier recovery costs
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=supplier_recovery_cost;y=created_at?options[supplierNumber]=50090&options[factory]=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for VWC for the supplier recovery costs as extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=supplier_recovery_cost;y=created_at?options[supplierNumber]=50090&options[factory]=/locations/11"
    Then the response status code should be 403

  Scenario: Get a report for VWC for the supplier recovery costs as vendor user not authorized for supplier
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=supplier_recovery_cost;y=created_at?options[supplierNumber]=50090&options[factory]=/locations/11"
    Then the response status code should be 403

  Scenario: Get a report for VWC for the supplier recovery costs as vendor user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=supplier_recovery_cost;y=created_at?options[supplierNumber]=DAN0013&options[factory]=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for VWC closed rate past last year
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=vwc_closed_rate;y=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"


  Scenario: Get a report for VWC resolved rate past last year
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/purchasing/vendor_warranty_claims;x=vwc_resolved_rate;y=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for ESR resolved by sso and status
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "reports/resource=/sales/equipment_shipping_records;x=sso.name;y=status"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for CRAB resolved by code, product and period
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "reports/resource=/quality/crabs;x=reportByPeriodByCodeByProduct;y=?options[factory]=/locations/11&options[from]=2019-12-19&options[to]=2021-12-19&options[product][]=/sales/products/31"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for CRAB resolved by code, family and period
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "reports/resource=/quality/crabs;x=reportByPeriodByCodeByFamily;y=?options[factory]=/locations/11&options[from]=2019-12-19&options[to]=2021-12-19&options[family][]=/sales/product_families/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for CRAB resolved by product over the past 12 months
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "reports/resource=/quality/crabs;x=reportLastYearByProduct;y=?options[factory]=/locations/11&options[product][]=/sales/products/31"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for CRAB resolved by family over the past 12 months
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "reports/resource=/quality/crabs;x=reportLastYearByFamily;y=?options[factory]=/locations/11&options[family][]=/sales/product_families/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for factory dashboard of ODP
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "reports/resource=/equipment_records;x=factory;y=dashboard"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for ODP stability
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "reports/resource=/equipment_records;x=stability;y=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for ODP overview
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "reports/resource=/equipment_records;x=odp_overview;y=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for TTS satisfaction KPI
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "reports/resource=/mis/trouble_tickets;x=satisfaction;y=week"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "reports/resource=/mis/trouble_tickets;x=satisfaction;y=month?options[region][]=/regions/2&options[application][]=/mis/applications/1&options[misAssignee][]=/people/99"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for TTS Status KPI
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "reports/resource=/mis/trouble_tickets;x=status;y=module.application.name"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for TTS By Type and Module KPI
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "reports/resource=/mis/trouble_tickets;x=module.name;y=type.type"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "reports/resource=/mis/trouble_tickets;x=module.name;y=type.type?options[createdAt][after]=2020-01-01&options[application][]=/mis/applications/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for SCAR for the history of supplier
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/quality/supplier_corrective_action_requests;x=supplier_history;y=created_at?options[supplierNumber]=50090&options[factory]=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for SCAR for the history of supplier as vendor user with no access on supplier
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/quality/supplier_corrective_action_requests;x=supplier_history;y=created_at?options[supplierNumber]=50090&options[factory]=/locations/11"
    Then the response status code should be 403

  Scenario: Get a report for SCAR for the history of supplier as vendor user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/quality/supplier_corrective_action_requests;x=supplier_history;y=created_at?options[supplierNumber]=DAN0013&options[factory]=/locations/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"

  Scenario: Get a report for SCAR for the history of supplier as extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/quality/supplier_corrective_action_requests;x=supplier_history;y=created_at?options[supplierNumber]=50090&options[factory]=/locations/11"
    Then the response status code should be 403