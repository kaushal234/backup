Feature: Test ESR cost double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create an ESR cost in API database triggers double-write on legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/equipment_shipping_record_costs" with body:
    """
    {
      "costDate": "2023-09-20T00:00:00-0400",
      "type": "Customs duties",
      "description": "test",
      "currency": "/finance/currencies/1",
      "price": 150,
      "equipmentShippingRecord": "/sales/equipment_shipping_records/1"
    }
    """
    Then the response status code should be 201
    Then a new row has been inserted in the legacy table "mod_costs"
    Then the column date from the mod_costs legacy table has been inserted with string "2023-09-20"
    Then the column "parent_id" from the "mod_costs" legacy table has been inserted with integer 717
    Then the column type from the mod_costs legacy table has been inserted with string "Customs duties"
    Then the column description from the mod_costs legacy table has been inserted with string "test"
    Then the column cur from the mod_costs legacy table has been inserted with string "USD"
    Then the column price from the mod_costs legacy table has been inserted with integer 150

  Scenario: Update an ESR cost in API database triggers double-write on legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/equipment_shipping_record_costs/1" with body:
    """
    {
      "costDate": "2023-09-22T00:00:00-0400",
      "type": "Others",
      "description": "test update",
      "currency": "/finance/currencies/2",
      "price": 160,
      "equipmentShippingRecord": "/sales/equipment_shipping_records/1"
    }
    """
    Then the response status code should be 200
    Then the column date from the mod_costs legacy table has been updated with string "2023-09-22"
    Then the column type from the mod_costs legacy table has been updated with string "Others"
    Then the column description from the mod_costs legacy table has been updated with string "test update"
    Then the column cur from the mod_costs legacy table has been updated with string "TWD"
    Then the column price from the mod_costs legacy table has been updated with integer 160

  Scenario: Delete an ESR cost in API database triggers double-write on legacy database
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "Delete" request to "/sales/equipment_shipping_record_costs/1"
    Then the response status code should be 204
    Then a row has been deleted in the legacy table mod_costs
