Feature: Test Equipment Record customer files API

  Scenario: Filters are declared on equipment record files
    Given the class "LegacyBundle\Entity\EquipmentRecordFile" is exposed on the API
    Then the filter "parentId" should be available and its type should be "int"

  Scenario: As an extranet user I can get the list of Equipment Record customer files
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/legacy/equipment_record_files"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/LegacyBundle/fixtures/json/equipment_record_file/schemas/equipment_record_files.json"

  Scenario: As an extranet user I can get the customer files of an accessible equipment
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/legacy/equipment_record_files?parentId=37454"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/LegacyBundle/fixtures/json/equipment_record_file/schemas/equipment_record_files.json"
    And the JSON node "hydra:totalItems" should be equal to "1"
    And the JSON node "hydra:member[0].displayFilename" should be equal to "customer_manual.pdf"

  Scenario: As an extranet user I cannot see customer files of an equipment I have no access to
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/legacy/equipment_record_files?parentId=37455"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to "0"

  Scenario: As an extranet user I get a 404 on a customer file I cannot access
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/legacy/equipment_record_files/6569"
    Then the response status code should be 404
