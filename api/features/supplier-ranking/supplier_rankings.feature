Feature: Test Supplier Rankings

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Purchasing\SupplierRanking\SupplierRanking" is exposed on the API
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "order[supplier.code]" should be available and its type should be "string"
    And the filter "order[supplier.name]" should be available and its type should be "string"
    And the filter "order[lastReviewAt]" should be available and its type should be "string"
    And the filter "order[lastScreeningAt]" should be available and its type should be "string"
    And the filter "order[classification.name]" should be available and its type should be "string"
    And the filter "order[revenue]" should be available and its type should be "string"
    And the filter "order[supplier.location.name]" should be available and its type should be "string"
    And the filter "lastReviewAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "lastReviewAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "lastScreeningAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "lastScreeningAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "classification" should be available and its type should be "string"
    And the filter "expertiseLevel" should be available and its type should be "string"
    And the filter "supplier.code" should be available and its type should be "string"
    And the filter "supplier.name" should be available and its type should be "string"
    And the filter "lastReviewBy" should be available and its type should be "string"
    And the filter "lastScreeningBy" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"
    And the filter "classification.isSupplierApproved" should be available and its type should be "bool"
    And the filter "columns" should be available and its type should be "string"
    And the filter "order[supplier.currency.name]" should be available and its type should be "string"
    And the filter "order[classification.isSupplierApproved]" should be available and its type should be "string"
    And the filter "order[nextReviewAt]" should be available and its type should be "string"
    And the filter "order[expertiseLevel.name]" should be available and its type should be "string"
    And the filter "order[nextReviewAt]" should be available and its type should be "string"
    And the filter "order[supplier.masterBuyer.lastname]" should be available and its type should be "string"
    And the filter "criteria_7" should be available and its type should be "array"
    And the filter "criteria_8" should be available and its type should be "string"
    And the filter "criteria_9" should be available and its type should be "string"

  Scenario: Request all supplier rankings for CPO
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_rankings.json"
    And the JSON node "hydra:totalItems" should be equal to 10

  Scenario: Request supplier rankings with filters for CPO
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings?order[supplier.code]=asc&location=/locations/36&classification=/purchasing/supplier_ranking/classifications/5"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_rankings.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].supplier.location.name" should be equal to the string "location_factory_400"
    And the JSON node "hydra:member[0].classification.name" should be equal to the string "Suppressed"

  Scenario: Request a single supplier ranking for CPO
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_ranking.json"

  Scenario: Request all supplier rankings for buyer
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings?order[id]=ASC"
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_rankings.json"
    And the JSON node "hydra:totalItems" should be equal to 10

  Scenario: Request supplier rankings with filters for buyer
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings?supplier.code=ACE00"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_rankings.json"
    And the JSON node "hydra:totalItems" should be equal to 2
    And the JSON node "hydra:member[0].supplier.code" should be equal to the string "ACE0012"
    And the JSON node "hydra:member[0].supplier.location.name" should be equal to the string "location_factory"
    And the JSON node "hydra:member[0].nextReviewAt" should not be null

  Scenario: Request a single supplier ranking for buyer with revenue < 100000
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings/6"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_ranking.json"
    And the JSON node "revenue" should be equal to 444.5
    And the JSON node "nextReviewAt" should be null
    And the JSON node "notations[0].enabled" should be true
    And the JSON node "notations[1].enabled" should be false
    And the JSON node "notations[2].enabled" should be false

  Scenario: Request a single supplier ranking for buyer with revenue between 100000 and 200000
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings/8"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_ranking.json"
    And the JSON node "revenue" should be equal to 157416
    And the JSON node "notations[0].enabled" should be true
    And the JSON node "notations[1].enabled" should be false
    And the JSON node "notations[2].enabled" should be true

  Scenario: Request a single supplier ranking for buyer with revenue > 100000
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings/10"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_ranking.json"
    And the JSON node "revenue" should be equal to 227861.25
    And the JSON node "notations[0].enabled" should be true
    And the JSON node "notations[1].enabled" should be true
    And the JSON node "notations[2].enabled" should be true

  Scenario: Request supplier rankings with filters on buyer
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings?buyer=/people/60"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_rankings.json"
    And the JSON node "hydra:totalItems" should be equal to 2

  Scenario: Update simple supplier ranking
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/supplier_ranking/supplier_rankings/10" with body:
    """
    {
      "expertiseLevel": "/purchasing/supplier_ranking/expertise_levels/2",
      "classification": "/purchasing/supplier_ranking/classifications/2",
      "lastScreeningAt": "2020-01-01 23:42:00"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_ranking.json"
    And the JSON node "expertiseLevel.name" should be equal to the string "Specialist"
    And the JSON node "classification.name" should be equal to the string "Monitored"
    And the JSON node "lastReviewAt" should be newer than 1 minute ago
    And the JSON node "lastScreeningAt" should be equal to the string "2020-01-01T23:42:00-05:00"
    And the JSON node "lastScreeningBy.@id" should be equal to the string "/people/12"

  Scenario: Test locked threshold
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/supplier_ranking/supplier_rankings/9" with body:
    """
    {
      "notations": [
        {
          "@id": "/notations/supplierRanking=9;criteria=1",
          "notation": 1
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_ranking.json"
    And the JSON node "classification.name" should be equal to the string "Locked"
    And an email should have been sent asynchronously with subject "ACE HARDWARE classification has been decreased"
    And this asynchronous email should be sent to "user-cpo@tld.fr"
    And this asynchronous email should be sent to "user-gceo@tld.fr"

  Scenario: Test monitored threshold
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/supplier_ranking/supplier_rankings/9" with body:
    """
    {
      "notations": [
        {
          "@id": "/notations/supplierRanking=9;criteria=1",
          "notation": 4
        },
        {
          "@id": "/notations/supplierRanking=9;criteria=2",
          "notation": 4
        },
        {
          "@id": "/notations/supplierRanking=9;criteria=3",
          "notation": 4
        },
        {
          "@id": "/notations/supplierRanking=9;criteria=9",
          "notation": 4
        },
        {
          "@id": "/notations/supplierRanking=9;criteria=6",
          "notation": 4
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_ranking.json"
    And the JSON node "classification.name" should be equal to the string "Monitored"

  Scenario: Request all supplier rankings for MLM
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_rankings.json"
    And the JSON node "hydra:totalItems" should be equal to 10

  Scenario: Request all supplier rankings for QAM
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_rankings.json"
    And the JSON node "hydra:totalItems" should be equal to 10

  Scenario: Denied access for PSM
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings"
    Then the response status code should be 403

  Scenario: Get list of supplier ranking of vendor user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_rankings.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].supplier.code" should be equal to the string "ALL0030"

  Scenario: Denied access for vendor user with wrong supplier number
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings/1"
    Then the response status code should be 403

  Scenario: New supplier should create new ranking
    Given I authenticate as the authorized application "ION"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/suppliers" with body:
    """
    {
      "name": "NEW SUPPLIER FOR RANKING",
      "code": "RANKING001",
      "masterBusinessUnit": "420",
      "country": "FR",
      "status": "ACTIVE",
      "currency": "EUR",
      "buyFrom": [
        {
          "locationCode": "420",
          "buyerId": 60
        }
      ]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier/schemas/supplier.json"
    Then I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings?supplier.code=RANKING001"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_rankings.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].supplier.code" should be equal to the string "RANKING001"
    And the JSON node "hydra:member[0].supplier.name" should be equal to the string "NEW SUPPLIER FOR RANKING"
    And the JSON node "hydra:member[0].disabledAt" should be null
    And the JSON node "hydra:member[0].classification" should be null

  Scenario: Remove master BU of supplier should disable ranking
    Given I authenticate as the authorized application "ION"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/suppliers" with body:
    """
    {
      "name": "SUPPLIER UPDATE FOR RANKING WITH NO BU",
      "code": "RANKING002",
      "masterBusinessUnit": "",
      "country": "FR",
      "status": "ACTIVE",
      "currency": "EUR",
      "buyFrom": [
        {
          "locationCode": "420",
          "buyerId": 60
        }
      ]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier/schemas/supplier.json"
    Then I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings?supplier.code=RANKING002"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_rankings.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].supplier.code" should be equal to the string "RANKING002"
    And the JSON node "hydra:member[0].supplier.name" should be equal to the string "SUPPLIER UPDATE FOR RANKING WITH NO BU"
    And the JSON node "hydra:member[0].disabledAt" should not be null

  Scenario: Add master BU of supplier should re enable ranking
    Given I authenticate as the authorized application "ION"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/suppliers" with body:
    """
    {
      "name": "SUPPLIER UPDATE FOR RANKING WITH NEW BU",
      "code": "RANKING002",
      "masterBusinessUnit": "420",
      "country": "FR",
      "status": "ACTIVE",
      "currency": "EUR",
      "buyFrom": [
        {
          "locationCode": "420",
          "buyerId": 60
        }
      ]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier/schemas/supplier.json"
    Then I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings?supplier.code=RANKING002"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_rankings.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].supplier.code" should be equal to the string "RANKING002"
    And the JSON node "hydra:member[0].supplier.name" should be equal to the string "SUPPLIER UPDATE FOR RANKING WITH NEW BU"
    And the JSON node "hydra:member[0].disabledAt" should be null

  Scenario: Update supplier with INACTIVE status should disable ranking
    Given I authenticate as the authorized application "ION"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/purchasing/suppliers" with body:
    """
    {
      "name": "SUPPLIER UPDATE FOR RANKING WITH INACTIVE",
      "code": "RANKING002",
      "masterBusinessUnit": "420",
      "country": "FR",
      "status": "INACTIVE",
      "currency": "EUR",
      "buyFrom": [
        {
          "locationCode": "420",
          "buyerId": 60
        }
      ]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier/schemas/supplier.json"
    Then I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings?supplier.code=RANKING002"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_rankings.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].supplier.code" should be equal to the string "RANKING002"
    And the JSON node "hydra:member[0].supplier.name" should be equal to the string "SUPPLIER UPDATE FOR RANKING WITH INACTIVE"
    And the JSON node "hydra:member[0].disabledAt" should not be null

  Scenario: Download excel supplier ranking should be possible
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings?columns=supplierNumber,supplierName,supplier.country,supplier.location,supplier.masterBuyer,expertiseLevel.name,revenue,supplier.currency,cost,logistic,communicationTransparencyResponsiveness,productFieldSupport,environmentalSocialGovernance,antiCorruption,cybersecurity,codeEthic,contracts,esg,iso14001,iso9001,minutesOfMeeting,others,prices,qualification,classification.name,isSupplierApproved,lastReviewAt,lastReviewBy,nextReviewAt,lastScreeningAt,lastScreeningBy"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Supplier Number | Supplier Name | Supplier Country | Master Bu | Buyer | Expertise Level | Total Revenue Last Year | Currency | Cost | Logistic | Communication Transparency Responsiveness | Product Field Support | Environmental Social Governance | Anti Corruption | Cybersecurity | Code Ethic | Contracts | Esg | Iso14001 | Iso9001 | Minutes Of Meeting | Others | Prices | Qualification | Classification | Is Supplier Approved | Last Review At | Last Review By | Next Review At | Last Screening At | Last Screening By |