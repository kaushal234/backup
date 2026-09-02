Feature: Test ESR double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create an ESRL in API database triggers double-write on legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/equipment_shipping_record_lines" with body:
    """
    {
      "equipmentRecord": "/equipment_records/7",
      "estimatedPickUpDate": "2023-08-24T00:00:00-0400",
      "vesselLoadingDate": "2023-08-25T00:00:00-0400",
      "estimatedArrivalDate": "2023-08-25T00:00:00-0400",
      "actualArrivalDate": null,
      "equipmentShippingRecord": "/sales/equipment_shipping_records/1"
    }
    """
    Then the response status code should be 201
    Then a new row has been inserted in the legacy table "esrl"
    And the column "erid" from the "esrl" legacy table has been inserted with integer 37461
    Then the column "parent_id" from the "esrl" legacy table has been inserted with integer 717
    Then the column dt_shipped from the esrl legacy table has been inserted with string "2023-08-25"
    Then the column dt_estimated from the esrl legacy table has been inserted with string "2023-08-25"
    Then the column dt_pick_up from the esrl legacy table has been inserted with string "2023-08-24"
    Then the column dt_arrived from the esrl legacy table has been inserted

  Scenario: Delete an ESRL in API database triggers double-write on legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "Delete" request to "/sales/equipment_shipping_record_lines/3"
    Then the response status code should be 204
    Then a row has been deleted in the legacy table esrl