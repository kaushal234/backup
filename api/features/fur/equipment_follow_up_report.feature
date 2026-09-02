Feature: Test Follow Up Reports API

  Scenario: Request all Follow Up Reports without being authenticated
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_follow_up_reports"
    Then the response status code should be 401

  Scenario: Request all Follow Up Reports
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_follow_up_reports"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_follow_up_report/schemas/equipment_follow_up_reports.json"
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_follow_up_reports"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_follow_up_report/schemas/equipment_follow_up_reports.json"

  Scenario: Request all Follow Up Reports should automatically filter on XU if they are creators
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_follow_up_reports"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_follow_up_report/schemas/equipment_follow_up_reports.json"
    And the JSON node "hydra:totalItems" should be equal to 1

  Scenario: Request all Follow Up Reports should automatically filter on XU if they are linked to the end user
    Given I authenticate as the extranet user "contract-enduser@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_follow_up_reports"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_follow_up_report/schemas/equipment_follow_up_reports.json"
    And the JSON node "hydra:totalItems" should be equal to 1

  Scenario: Request all Follow Up Reports should automatically filter on XU if they are linked to the buyer
    Given I authenticate as the extranet user "contract-enduser@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_follow_up_reports"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_follow_up_report/schemas/equipment_follow_up_reports.json"
    And the JSON node "hydra:totalItems" should be equal to 1

    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_follow_up_reports"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_follow_up_report/schemas/equipment_follow_up_reports.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Support\EquipmentFollowUpReport" is exposed on the API
    And the filter "equipmentRecord.legacyId" should be available and its type should be "int"
    And the filter "equipmentRecord.serialNumber" should be available and its type should be "string"
    And the filter "equipmentRecord" should be available and its type should be "string"
    And the filter "createdBy" should be available and its type should be "string"
    And the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "hourmeterDate[after]" should be available and its type should be "DateTimeInterface"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "order[hourmeterDate]" should be available and its type should be "string"
    And the filter "order[equipmentRecord.serialNumber]" should be available and its type should be "string"

  Scenario: Request a single Unit Follow Up Report
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_follow_up_reports/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_follow_up_report/schemas/equipment_follow_up_report.json"

  Scenario: Create Unit Follow Up Report without perms
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with the body "tests/fixtures/json/equipment_follow_up_report/dummies/post.json"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with the body "tests/fixtures/json/equipment_follow_up_report/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create Unit Follow Up Report with wrong dates should fail
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with the body "tests/fixtures/json/equipment_follow_up_report/dummies/post.json" and replace "hourmeterDate" by the date "-200 days"
    Then the response status code should be 422
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with the body "tests/fixtures/json/equipment_follow_up_report/dummies/post.json" and replace "hourmeterDate" by the date "+50 days"
    Then the response status code should be 422

  Scenario: Create Unit Follow Up Report
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with the body "tests/fixtures/json/equipment_follow_up_report/dummies/post.json" and replace "hourmeterDate" by the date "-5 days"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_follow_up_report/schemas/equipment_follow_up_report.json"
    And the JSON node "equipmentRecord.@id" should be equal to the string "/equipment_records/1"
    And the JSON node "comment" should be equal to the string "A nice comment"
    And the JSON node "hourmeter" should be equal to the number 84
    And the JSON node "operationalStatus.@id" should be equal to the string "/unit_operational_statuses/MCF"
    And the JSON node "accident" should be null
    And the JSON node "maintenance" should be null
    # Users should not be not accessible
    And the JSON node "createdBy.@id" should be equal to the string "/sales/extranet_users/202"
    And the JSON node "updatedBy.@id" should be equal to the string "/sales/extranet_users/202"

  Scenario: Create Unit Follow Up Report with accident and maintenance
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with the body "tests/fixtures/json/equipment_follow_up_report/dummies/post_full.json" and replace "hourmeterDate" by the date "-5 days"
    Then the response status code should be 201
    And the JSON node "accident.@id" should exist
    And the JSON node "accident.date" should contain "2012-08-20"
    And the JSON node "accident.comment" should be equal to "Et aut ipsum possimus consequuntur."
    And the JSON node "accident.humanInjuries" should be false
    And the JSON node "accident.planeDamages" should be true
    And the JSON node "accident.environmentDamages" should be true
    And the JSON node "accident.equipmentDamages" should be false
    And the JSON node "maintenance.@id" should exist
    And the JSON node "maintenance.date" should contain "2012-08-20"
    And the JSON node "maintenance.comment" should be equal to "Dolores aut velit illo illum sint voluptas."
    And the JSON node "maintenance.type" should be equal to the number 1000

  Scenario: Update Unit Follow Up Report without perms should fail
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_follow_up_reports/26" with the body "tests/fixtures/json/equipment_follow_up_report/dummies/put.json" and replace "hourmeterDate" by the date "-5 days"
    Then the response status code should be 403

  Scenario: Update Unit Follow Up Report without perms should fail
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_follow_up_reports/26" with the body "tests/fixtures/json/equipment_follow_up_report/dummies/put.json" and replace "hourmeterDate" by the date "-5 days"
    Then the response status code should be 403

  Scenario: Update Unit Follow Up Report
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    # We are updating the newly created
    When I send a "PUT" request to "/equipment_follow_up_reports/26" with the body "tests/fixtures/json/equipment_follow_up_report/dummies/put.json" and replace "hourmeterDate" by the date "-5 days"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_follow_up_report/schemas/equipment_follow_up_report.json"
    And the JSON node "comment" should be equal to the string "Another nice comment"
    And the JSON node "hourmeter" should be equal to the number 84
    And the JSON node "operationalStatus.@id" should be equal to the string "/unit_operational_statuses/MCP"
    And the JSON node "odometer" should be equal to the number 7
    And the JSON node "odometerDate" should contain "2016-02-17"
    And the JSON node "accident" should be null
    And the JSON node "maintenance" should be null
    # Users should not be not accessible
    And the JSON node "equipmentRecord.@id" should be equal to the string "/equipment_records/1"
    And the JSON node "createdBy.@id" should be equal to the string "/sales/extranet_users/202"
    And the JSON node "updatedBy.@id" should be equal to the string "/sales/extranet_users/202"

  Scenario: Update Unit Follow Up Report (full)
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    # We are updating the newly created
    When I send a "PUT" request to "/equipment_follow_up_reports/27" with the body "tests/fixtures/json/equipment_follow_up_report/dummies/put_full.json" and replace "hourmeterDate" by the date "-5 days"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_follow_up_report/schemas/equipment_follow_up_report.json"
    And the JSON node "accident.@id" should exist
    And the JSON node "accident.date" should contain "2014-08-20"
    And the JSON node "accident.comment" should be equal to "what"
    And the JSON node "accident.humanInjuries" should be true
    And the JSON node "accident.planeDamages" should be false
    And the JSON node "accident.environmentDamages" should be false
    And the JSON node "accident.equipmentDamages" should be true
    And the JSON node "maintenance.@id" should exist
    And the JSON node "maintenance.date" should contain "2018-08-20"
    And the JSON node "maintenance.comment" should be equal to "daf"
    And the JSON node "maintenance.type" should be equal to the number 500

  Scenario: User can't create follow up report with a higher hourmeter
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with body and replace "hourmeterDate" by the date "-5 days":
    """
      {
        "equipmentRecord": "/equipment_records/2",
        "comment": "comment te dire ce que je ne peux pas écrire...",
        "equipmentRecord": "/equipment_records/2",
        "hourmeter": 100,
        "hourmeterDate": "1984-01-07",
        "operationalStatus": "/unit_operational_statuses/MCF"
      }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "hourmeter: The hourmeter totalizer calculated is 470 but should be inferior to 420"
    And the JSON node "violations[0].propertyPath" should be equal to "hourmeter"
    And the JSON node "violations[0].message" should contain "The hourmeter totalizer calculated is 470 but should be inferior to 420"

  Scenario: User can't create follow up report with a smaller amount of time
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with body and replace "hourmeterDate" by the date "now":
    """
      {
        "equipmentRecord": "/equipment_records/2",
        "comment": "comment te dire ce que je ne peux pas écrire...",
        "equipmentRecord": "/equipment_records/2",
        "hourmeter": 1,
        "hourmeterDate": "1984-01-07",
        "operationalStatus": "/unit_operational_statuses/MCF"
      }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "hourmeter: The hourmeter totalizer calculated is 371 but should be superior or equal to 420"
    And the JSON node "violations[0].propertyPath" should be equal to "hourmeter"
    And the JSON node "violations[0].message" should contain "The hourmeter totalizer calculated is 371 but should be superior or equal to 420"

  Scenario: User can't create follow up report with a date over 90 days
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with body and replace "hourmeterDate" by the date "-91 days":
    """
      {
        "equipmentRecord": "/equipment_records/2",
        "comment": "comment te dire ce que je ne peux pas écrire...",
        "equipmentRecord": "/equipment_records/2",
        "hourmeter": 150,
        "hourmeterDate": "1984-01-07",
        "operationalStatus": "/unit_operational_statuses/MCF"
      }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "hourmeterDate: This value should be greater than or equal to"
    And the JSON node "violations[0].propertyPath" should be equal to "hourmeterDate"

  Scenario: User can create follow up report that will fit with already registered hourmeters
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with body and replace "hourmeterDate" by the date "-90 days":
    """
      {
        "equipmentRecord": "/equipment_records/2",
        "comment": "comment te dire ce que je ne peux pas écrire...",
        "equipmentRecord": "/equipment_records/2",
        "hourmeter": 120,
        "hourmeterDate": "1984-01-07",
        "operationalStatus": "/unit_operational_statuses/MCF"
      }
    """
    Then the response status code should be 201
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with body and replace "hourmeterDate" by the date "-70 days":
    """
      {
        "equipmentRecord": "/equipment_records/2",
        "comment": "comment te dire ce que je ne peux pas écrire...",
        "equipmentRecord": "/equipment_records/2",
        "hourmeter": 200,
        "hourmeterDate": "1984-01-07",
        "operationalStatus": "/unit_operational_statuses/MCF"
      }
    """
    Then the response status code should be 201
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with body and replace "hourmeterDate" by the date "-49 days":
    """
      {
        "equipmentRecord": "/equipment_records/2",
        "comment": "comment te dire ce que je ne peux pas écrire...",
        "equipmentRecord": "/equipment_records/2",
        "hourmeter": 0,
        "hourmeterDate": "1984-01-07",
        "operationalStatus": "/unit_operational_statuses/MCF"
      }
    """
    Then the response status code should be 201
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with body and replace "hourmeterDate" by the date "-45 days":
    """
      {
        "equipmentRecord": "/equipment_records/2",
        "comment": "comment te dire ce que je ne peux pas écrire...",
        "equipmentRecord": "/equipment_records/2",
        "hourmeter": 2,
        "hourmeterDate": "1984-01-07",
        "operationalStatus": "/unit_operational_statuses/MCF"
      }
    """
    Then the response status code should be 201
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with body and replace "hourmeterDate" by the date "-30 days":
    """
      {
        "equipmentRecord": "/equipment_records/2",
        "comment": "comment te dire ce que je ne peux pas écrire...",
        "equipmentRecord": "/equipment_records/2",
        "hourmeter": 10,
        "hourmeterDate": "1984-01-07",
        "operationalStatus": "/unit_operational_statuses/MCF"
      }
    """
    Then the response status code should be 201
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with body and replace "hourmeterDate" by the date "-25 days":
    """
      {
        "equipmentRecord": "/equipment_records/2",
        "comment": "comment te dire ce que je ne peux pas écrire...",
        "equipmentRecord": "/equipment_records/2",
        "hourmeter": 20,
        "hourmeterDate": "1984-01-07",
        "operationalStatus": "/unit_operational_statuses/MCF"
      }
    """
    Then the response status code should be 201
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with body and replace "hourmeterDate" by the date "-25 days":
    """
      {
        "equipmentRecord": "/equipment_records/2",
        "comment": "comment te dire ce que je ne peux pas écrire...",
        "equipmentRecord": "/equipment_records/2",
        "hourmeter": 21,
        "hourmeterDate": "1984-01-07",
        "operationalStatus": "/unit_operational_statuses/MCF"
      }
    """
    Then the response status code should be 201
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with body and replace "hourmeterDate" by the date "-25 days":
    """
      {
        "equipmentRecord": "/equipment_records/2",
        "comment": "comment te dire ce que je ne peux pas écrire...",
        "equipmentRecord": "/equipment_records/2",
        "hourmeter": 21,
        "hourmeterDate": "1984-01-07",
        "operationalStatus": "/unit_operational_statuses/MCF"
      }
    """
    Then the response status code should be 201
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with body and replace "hourmeterDate" by the date "-25 days":
    """
      {
        "equipmentRecord": "/equipment_records/2",
        "comment": "comment te dire ce que je ne peux pas écrire...",
        "equipmentRecord": "/equipment_records/2",
        "hourmeter": 30,
        "hourmeterDate": "1984-01-07",
        "operationalStatus": "/unit_operational_statuses/MCF"
      }
    """
    Then the response status code should be 201
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with body and replace "hourmeterDate" by the date "now":
    """
      {
        "equipmentRecord": "/equipment_records/2",
        "comment": "comment te dire ce que je ne peux pas écrire...",
        "equipmentRecord": "/equipment_records/2",
        "hourmeter": 60,
        "hourmeterDate": "1984-01-07",
        "operationalStatus": "/unit_operational_statuses/MCF"
      }
    """
    Then the response status code should be 201

  Scenario: XU linked to an expired contract should not be allowed to create FUR on unit linked to this contract if not linked via CRT
    Given I authenticate as the extranet user "contract-enduser@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with body:
    """
    {
      "equipmentRecord": "/equipment_records/14",
      "comment": "A nice comment",
      "hourmeter": 84,
      "hourmeterDate": "1984-01-07",
      "operationalStatus": "/unit_operational_statuses/MCF"
    }
    """
    Then the response status code should be 403

  Scenario: XU linked to an active contract as end user should be able to create FUR on unit linked to this contract
    Given I authenticate as the extranet user "contract-enduser@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with body and replace "hourmeterDate" by the date "now":
    """
    {
      "equipmentRecord": "/equipment_records/13",
      "comment": "A nice comment",
      "hourmeter": 8000,
      "hourmeterDate": "1984-01-07",
      "operationalStatus": "/unit_operational_statuses/MCF"
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to the string "/equipment_follow_up_reports/38"

  Scenario: Update Unit Follow Up Report under contract when the user is linked as end user should be possible even if not the creator
    Given I authenticate as the extranet user "contract-enduser-extra@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
     # We are updating the newly created
    When I send a "PUT" request to "/equipment_follow_up_reports/38" with body:
     """
    {
      "comment": "A nicer comment"
    }
    """
    Then the response status code should be 200

  Scenario: XU linked to an active contract as buyer should not be able to create FUR on unit linked to this contract
    Given I authenticate as the extranet user "contract-buyer@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_follow_up_reports" with body and replace "hourmeterDate" by the date "now":
    """
    {
      "equipmentRecord": "/equipment_records/13",
      "comment": "A nice comment",
      "hourmeter": 8000,
      "hourmeterDate": "1984-01-07",
      "operationalStatus": "/unit_operational_statuses/MCF"
    }
    """
    Then the response status code should be 403

  Scenario: Update Unit Follow Up Report under contract when the user is linked as buyer should not be possible
    Given I authenticate as the extranet user "contract-buyer@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_follow_up_reports/38" with body:
     """
    {
      "comment": "A nicer comment"
    }
    """
    Then the response status code should be 403
