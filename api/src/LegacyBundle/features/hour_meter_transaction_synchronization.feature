Feature: Test Hour meter transactions double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create an hour meter transaction should double write in the legacy database and should update ER legacy table
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "POST" request to "support/equipment_record/equipment_record_hour_meter_transactions" with body:
    """
    {
      "hourMeter": 55,
      "equipmentRecord": "/equipment_records/15"
    }
    """
    Then the response status code should be 201
    Then a new row has been inserted in the legacy table "service_hourmeter"
    And the column "hourmeter" from the "service_hourmeter" legacy table has been inserted with integer 55
    And the column "parent_id" from the "service_hourmeter" legacy table has been inserted with integer 37469
    And the column "module_id" from the "service_hourmeter" legacy table has been inserted with integer 37469
    And the column "module" from the "service_hourmeter" legacy table has been inserted with string "ER"
    And the column "hours" from the "service" legacy table has been updated with integer 55
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "POST" request to "support/equipment_record/warranty_claim_hour_meter_transactions" with body:
    """
    {
      "hourMeter": 56,
      "equipmentRecord": "/equipment_records/15",
      "warrantyClaimLegacyId": 17
    }
    """
    Then the response status code should be 201
    Then a new row has been inserted in the legacy table "service_hourmeter"
    And the column "hourmeter" from the "service_hourmeter" legacy table has been inserted with integer 56
    And the column "parent_id" from the "service_hourmeter" legacy table has been inserted with integer 37469
    And the column "module_id" from the "service_hourmeter" legacy table has been inserted with integer 17
    And the column "module" from the "service_hourmeter" legacy table has been inserted with string "WC"
    And the column "hours" from the "service" legacy table has been updated with integer 56
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "POST" request to "support/equipment_record/customer_service_record_hour_meter_transactions" with body:
    """
    {
      "hourMeter": 57,
      "equipmentRecord": "/equipment_records/15",
      "customerServiceRecord": "/service/customer_service_records/3"
    }
    """
    Then the response status code should be 201
    Then a new row has been inserted in the legacy table "service_hourmeter"
    And the column "hourmeter" from the "service_hourmeter" legacy table has been inserted with integer 57
    And the column "parent_id" from the "service_hourmeter" legacy table has been inserted with integer 37469
    And the column "module_id" from the "service_hourmeter" legacy table has been inserted with integer 58644
    And the column "module" from the "service_hourmeter" legacy table has been inserted with string "CSR"
      And the column "hours" from the "service" legacy table has been updated with integer 57
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "POST" request to "/support/equipment_record/technician_on_call_hour_meter_transactions" with body:
    """
    {
        "hourMeter": 58,
        "technicianOnCall": "/service/technician_on_calls/4"
    }
    """
    Then the response status code should be 201
    Then a new row has been inserted in the legacy table "service_hourmeter"
    And the column "hourmeter" from the "service_hourmeter" legacy table has been inserted with integer 58
    And the column "parent_id" from the "service_hourmeter" legacy table has been inserted with integer 37469
    And the column "module_id" from the "service_hourmeter" legacy table has been inserted with integer 37033
    And the column "module" from the "service_hourmeter" legacy table has been inserted with string "TOC"
    And the column "hours" from the "service" legacy table has been updated with integer 58