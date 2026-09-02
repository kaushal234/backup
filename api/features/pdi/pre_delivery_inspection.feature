Feature: Test PreDeliveryInspection Entity

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\MIS\TroubleTicket\TroubleTicket" should only be available for intranet user

  Scenario: Request PDI as basic user should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/pre_delivery_inspections"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/pre_delivery_inspection/schemas/pre_delivery_inspections.json"

  Scenario: Create a PDI as basic user should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/pre_delivery_inspections" with body:
    """
    {
      "plannedAt": "2025-06-19 17:42:24",
      "equipmentRecord": "equipment_records/5"
    }
    """
    Then the response status code should be 403

  Scenario: Create a PDI as user-psm should be possible
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/pre_delivery_inspections" with body:
    """
    {
      "plannedAt": "2025-06-19 17:42:24",
      "equipmentRecord": "equipment_records/21"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/pre_delivery_inspection/schemas/pre_delivery_inspection.json"

  Scenario: Create a PDI as user-asm should be possible
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/pre_delivery_inspections" with body:
    """
    {
      "plannedAt": "2025-06-19 17:42:24",
      "equipmentRecord": "equipment_records/20"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/pre_delivery_inspection/schemas/pre_delivery_inspection.json"

  Scenario: Create a PDI for an EquipmentRecord with an open PDI should not be possible
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/pre_delivery_inspections" with body:
    """
    {
      "plannedAt": "2025-06-19 17:42:24",
      "equipmentRecord": "equipment_records/21"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should contain "An open inspection already exists for this equipment record."

  Scenario: As user-pse@tld.fr, reschedule a PDI should be possible
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/pre_delivery_inspections/3" with body:
    """
    {
      "plannedAt": "2025-07-19 15:42:24",
      "equipmentRecord": "equipment_records/21"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/pre_delivery_inspection/schemas/pre_delivery_inspection.json"
    And the JSON node "plannedAt" should be equal to the string "2025-07-19T15:42:24-04:00"
    