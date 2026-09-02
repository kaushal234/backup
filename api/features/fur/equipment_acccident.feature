Feature: Test Equipment Accidents API

  Scenario: Request all Equipment Accidents without being authenticated
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_accidents"
    Then the response status code should be 401

  Scenario: Request all Equipment Accidents
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_accidents"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_accident/schemas/equipment_accidents.json"
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_accidents"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_accident/schemas/equipment_accidents.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Support\EquipmentAccident" is exposed on the API
    Then the filter "followUpReport.equipmentRecord" should be available and its type should be "string"
    And the filter "followUpReport.equipmentRecord.legacyId" should be available and its type should be "int"
    And the filter "followUpReport.equipmentRecord.serialNumber" should be available and its type should be "string"
    And the filter "followUpReport.createdBy" should be available and its type should be "string"

  Scenario: Request a single Unit Equipment Accident
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_accidents/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_accident/schemas/equipment_accident.json"

  @resetFileTable
  Scenario: Upload a file to an accident
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/equipment_accidents/1/files" with file "file" "file.pdf"
    Then the response status code should be 201
    And an update log should have been inserted on resource "/equipment_accidents/1" with a changeset on the property "accidentFiles"
    # Check that the item schema is still valid
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_accidents/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_accident/schemas/equipment_accident.json"

  Scenario: Upload an invalid file to an accident
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/equipment_accidents/1/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "accidentFiles: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "accidentFiles"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download an accident file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_accidents/2/files/1"
    Then the response status code should be 404
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_accidents/1/files/1"
    Then the response status code should be 200
    Then the header "Content-Type" should be equal to "application/pdf"
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_accidents/1/files/1"
    Then the response status code should be 404

  Scenario: Create an accident should not be possible
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_accidents" with body:
    """
      {
        "something": "test"
      }
    """
    Then the response status code should be 405


  Scenario: Update an accident should not be possible
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_accidents/21" with body:
    """
      {
        "something": "test"
      }
    """
    Then the response status code should be 405
