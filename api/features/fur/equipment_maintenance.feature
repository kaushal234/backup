Feature: Test equipment maintenances API
  Scenario: Request all equipment maintenances without being authenticated
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_maintenances"
    Then the response status code should be 401

  Scenario: Request all equipment maintenances
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_maintenances"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_maintenance/schemas/equipment_maintenances.json"
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_maintenances"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_maintenance/schemas/equipment_maintenances.json"

  Scenario: Request all equipment maintenances filtered by ER
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_maintenances?followUpReport.equipmentRecord=/equipment_records/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_maintenance/schemas/equipment_maintenances.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Support\EquipmentMaintenance" is exposed on the API
    And the filter "followUpReport.equipmentRecord.legacyId" should be available and its type should be "int"
    And the filter "followUpReport.equipmentRecord.serialNumber" should be available and its type should be "string"
    And the filter "followUpReport.createdBy" should be available and its type should be "string"

  Scenario: Request a single Unit equipment maintenance
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_maintenances/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_maintenance/schemas/equipment_maintenance.json"

  @resetFileTable
  Scenario: Upload a file to a maintenance
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/equipment_maintenances/1/files" with file "file" "file.pdf"
    Then the response status code should be 201
    And an update log should have been inserted on resource "/equipment_maintenances/1" with a changeset on the property "maintenanceFiles"
    # Check that the item schema is still valid
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_maintenances/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_maintenance/schemas/equipment_maintenance.json"

  Scenario: Upload an invalid file to a maintenance
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/equipment_maintenances/1/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "maintenanceFiles: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "maintenanceFiles"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download a maintenance file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_maintenances/2/files/1"
    Then the response status code should be 404
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_maintenances/1/files/1"
    Then the response status code should be 200
    Then the header "Content-Type" should be equal to "application/pdf"
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_maintenances/1/files/1"
    Then the response status code should be 404

  Scenario: Create a maintenance should not be possible
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_maintenances" with body:
    """
      {
        "something": "test"
      }
    """
    Then the response status code should be 405


  Scenario: Update a maintenance should not be possible
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_maintenances/21" with body:
    """
      {
        "something": "test"
      }
    """
    Then the response status code should be 405
