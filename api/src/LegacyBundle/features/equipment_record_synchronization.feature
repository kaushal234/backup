Feature: Test ER double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Update an Equipment Serial should double write its serials collection in the legacy database
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "PUT" request to "/equipment_records/3" with body:
    """
    {
      "factoryComment": "factory",
      "serials": [
        {
          "component": "/equipment_serial_components/6",
          "serial": "Cerealkiller3"
        },
        {
          "@id": "/equipment_serials/7",
          "component": "/equipment_serial_components/4",
          "serial": "DISLIKER"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON node "serials" should have 2 elements
    And the JSON node "serials[1].component.name" should be equal to the string "SCHEM, BRAKING"
    # /equipment_serials/3 has been deleted
    Then a row where "id" with value 313233 has been deleted from "service_serials" legacy table
    Then a new row has been inserted in the legacy table "service_serials"
    And the column "parent_id" from the "service_serials" legacy table has been inserted with integer 37457
    And the column "serial" from the "service_serials" legacy table has been inserted with string "Cerealkiller3"
    # /equipment_serials/9 has been updated
    And the column "serial" from the "service_serials" legacy table has been updated with string "DISLIKER"

  Scenario: Allowed user can edit a equipment Record through ODP
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_records_odp/26" with body:
    """
    {
       "@id": "/equipment_records/26",
       "yellowTagDate": "2099-05-03 20:10:00",
       "estimatedGreenTagDate": "2099-05-03 20:10:00",
       "greenTagDate": "2099-05-03 20:10:00",
       "firstGreenTagDate": "2099-05-03 20:10:00",
       "dateShipped": "2099-05-03 20:10:00",
       "odpComment": "I TEST"
    }
    """
    Then the response status code should be 200
    And the column "dyt" from the "service" legacy table has been updated with a string containing "2099-05-03"
    And the column "dgt_rev" from the "service" legacy table has been updated with a string containing "2099-05-03"
    And the column "dgt_act" from the "service" legacy table has been updated with a string containing "2099-05-03"
    And the column "dgt_com" from the "service" legacy table has been updated with a string containing "2099-05-03"
    And the column "date_shipped" from the "service" legacy table has been updated with a string containing "2099-05-03"
    And the column "odp_note" from the "service" legacy table has been updated with a string containing "I TEST"
    And 2 new rows have been inserted in the legacy table "mod_logs"
