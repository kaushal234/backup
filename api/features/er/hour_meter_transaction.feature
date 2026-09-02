Feature: Test HourMeter Transaction API

  Scenario: Request all hour meter transactions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/equipment_record/hour_meter_transactions"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/hour_meter_transaction/schemas/hour_meter_transactions.json"

  Scenario: Resources should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Support\EquipmentRecord\HourMeterTransaction\HourMeterTransaction" should only be available for intranet user

  Scenario: Request a single hour meter transaction
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/support/equipment_record/hour_meter_transactions/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/hour_meter_transaction/schemas/hour_meter_transaction.json"

  Scenario: Create an hour meter transaction directly should not be allowed
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/equipment_record/hour_meter_transactions"
    Then the response status code should be 405

  Scenario: Create an SCM hour meter transaction should not be allowed
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/equipment_record/service_contract_maintenance_hour_meter_transactions"
    Then the response status code should be 404

  Scenario: Create an hour meter transaction for module should be allowed for basic user and hour meter of ER should be updated
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/equipment_record/equipment_record_hour_meter_transactions" with body:
    """
    {
      "hourMeter": 55,
      "equipmentRecord": "/equipment_records/15"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/15"
    Then the response status code should be 200
    And the JSON node "hourMeter" should be equal to 55
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/equipment_record/customer_service_record_hour_meter_transactions" with body:
    """
    {
      "hourMeter": 56,
      "equipmentRecord": "/equipment_records/15",
      "customerServiceRecord": "/service/customer_service_records/3"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/hour_meter_transaction/schemas/hour_meter_transaction.json"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/15"
    Then the response status code should be 200
    And the JSON node "hourMeter" should be equal to 56
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/equipment_record/technician_on_call_hour_meter_transactions" with body:
    """
    {
      "hourMeter": 57,
      "technicianOnCall": "/service/technician_on_calls/4"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/hour_meter_transaction/schemas/hour_meter_transaction.json"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/15"
    Then the response status code should be 200
    And the JSON node "hourMeter" should be equal to 57
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/equipment_record/warranty_claim_hour_meter_transactions" with body:
    """
    {
      "hourMeter": 58,
      "equipmentRecord": "/equipment_records/15",
      "warrantyClaimLegacyId": 17
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/hour_meter_transaction/schemas/hour_meter_transaction.json"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/15"
    Then the response status code should be 200
    And the JSON node "hourMeter" should be equal to 58

  Scenario: Create an hour meter transaction with value less than existing hourmeter of ER should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/equipment_record/equipment_record_hour_meter_transactions" with body:
    """
    {
      "hourMeter": 12,
      "equipmentRecord": "/equipment_records/15"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "The new hour meter should be equal or greater than the actual one."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/equipment_record/customer_service_record_hour_meter_transactions" with body:
    """
    {
      "hourMeter": 12,
      "equipmentRecord": "/equipment_records/15",
      "customerServiceRecord": "/service/customer_service_records/3"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "The new hour meter should be equal or greater than the actual one."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/equipment_record/technician_on_call_hour_meter_transactions" with body:
    """
    {
      "hourMeter": 12,
      "technicianOnCall": "/service/technician_on_calls/4"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "The new hour meter should be equal or greater than the actual one."
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/support/equipment_record/warranty_claim_hour_meter_transactions" with body:
    """
    {
      "hourMeter": 12,
      "equipmentRecord": "/equipment_records/15",
      "warrantyClaimLegacyId": 17
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "The new hour meter should be equal or greater than the actual one."

  Scenario: Update an hour meter transaction directly should not be allowed
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/equipment_record/hour_meter_transactions/1"
    Then the response status code should be 405

  Scenario: Update an hour meter transaction should only be allowed for hourMeter property
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/equipment_record/equipment_record_hour_meter_transactions/6" with body:
    """
    {
      "hourMeter": 61,
      "equipmentRecord": "/equipment_records/16"
    }
    """
    Then the response status code should be 200
    And the JSON node "hourMeter" should be equal to 61
    And the JSON node "equipmentRecord.@id" should be equal to the string "/equipment_records/15"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/15"
    Then the response status code should be 200
    And the JSON node "hourMeter" should be equal to 61
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/equipment_record/customer_service_record_hour_meter_transactions/7" with body:
    """
    {
      "hourMeter": 62,
      "equipmentRecord": "/equipment_records/16",
      "customerServiceRecord": "/service/customer_service_records/4"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/hour_meter_transaction/schemas/hour_meter_transaction.json"
    And the JSON node "hourMeter" should be equal to 62
    And the JSON node "equipmentRecord.@id" should be equal to the string "/equipment_records/15"
    And the JSON node "customerServiceRecord" should be equal to the string "/service/default_customer_service_records/3"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/15"
    Then the response status code should be 200
    And the JSON node "hourMeter" should be equal to 62
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/equipment_record/technician_on_call_hour_meter_transactions/8" with body:
    """
    {
      "hourMeter": 63,
      "equipmentRecord": "/equipment_records/16",
      "tocLegacyId": 17
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/hour_meter_transaction/schemas/hour_meter_transaction.json"
    And the JSON node "hourMeter" should be equal to 63
    And the JSON node "equipmentRecord.@id" should be equal to the string "/equipment_records/15"
    And the JSON node "tocLegacyId" should be equal to 37033
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/15"
    Then the response status code should be 200
    And the JSON node "hourMeter" should be equal to 63
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/equipment_record/warranty_claim_hour_meter_transactions/9" with body:
    """
    {
      "hourMeter": 64,
      "equipmentRecord": "/equipment_records/16",
      "warrantyClaimLegacyId": 18
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/hour_meter_transaction/schemas/hour_meter_transaction.json"
    And the JSON node "hourMeter" should be equal to 64
    And the JSON node "equipmentRecord.@id" should be equal to the string "/equipment_records/15"
    And the JSON node "warrantyClaimLegacyId" should be equal to 17
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_records/15"
    Then the response status code should be 200
    And the JSON node "hourMeter" should be equal to 64

  Scenario: Hour meter should be equal or greater than the existing one on the ER when updating hour meter transaction
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/support/equipment_record/equipment_record_hour_meter_transactions/6" with body:
    """
    {
      "hourMeter": 12
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "The new hour meter should be equal or greater than the actual one."

