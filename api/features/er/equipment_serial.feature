Feature: Test Equipment Serials API

  Scenario: Request all Serials without being authenticated
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_serials"
    Then the response status code should be 401

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Support\EquipmentSerial" is exposed on the API
    Then the filter "schematics" should be available and its type should be "bool"

  Scenario: Request all Serials
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_serials"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_serial/schemas/equipment_serials.json"

  Scenario: Request a single Serial
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_serials/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_serial/schemas/equipment_serial.json"

  Scenario: Update a Serial should not be allowed
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_serials/1"
    Then the response status code should be 405

  Scenario: Create a Serial should not be allowed to basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_serials"
    Then the response status code should be 403

  Scenario: Create a Serial
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_serials" with body:
    """
    {
        "serial": "2033537zerzerz",
        "component": "/equipment_serial_components/5",
        "equipmentRecord": "/equipment_records/1"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_serial/schemas/equipment_serial.json"
    And the JSON node "model" should be null
    And the JSON node "serial" should be equal to "2033537zerzerz"
    And the JSON node "brand" should be null

  Scenario: Create a serial already existing should fail
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_serials" with body:
    """
    {
        "serial": "2033537zerzerz",
        "component": "/equipment_serial_components/5",
        "equipmentRecord": "/equipment_records/1"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "already exist on this equipment record"

  Scenario: Delete a Serial
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/equipment_serials/5"
    Then the response status code should be 204

  Scenario: Delete a Serial should not be possible for user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/equipment_serials/1"
    Then the response status code should be 403

  Scenario: Filter serials by the schematics flag on an equipment record
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    # PDI2025 has 5 serials: 3 schematics (SCHEM, BRAKING / SCHEM, ELEC / SCHEM, HYD) and 2 non-schematics (REAR AXLE / PROGRAM)
    When I send a "GET" request to "/equipment_serials?equipmentRecord.serialNumber=PDI2025&schematics=true"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 3
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_serials?equipmentRecord.serialNumber=PDI2025&schematics=false"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 2

  Scenario: An extranet user can list the serials of an equipment record he has access to
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_serials?equipmentRecord.serialNumber=PDI2025&schematics=false"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_serial/schemas/equipment_serials.json"
    And the JSON node "hydra:totalItems" should be equal to 2

  Scenario: An extranet user cannot list the serials of an equipment record he has no access to
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    # The serial on REMI exists and is visible to an intranet user
    When I send a "GET" request to "/equipment_serials?equipmentRecord.serialNumber=REMI"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 1
    # But it is filtered out for an extranet user who is neither buyer, maintainer nor end user
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_serials?equipmentRecord.serialNumber=REMI"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 0