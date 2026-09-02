Feature: Test Equipment Serial double write API

  Background: Authenticate user
    Given I authenticate as the intranet user "user-superuser@tld.fr"

  Scenario: Create an Equipment Serial should double write serials in the legacy database
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "POST" request to "/equipment_serials" with body:
    """
    {
      "equipmentRecord": "/equipment_records/2",
      "model": "MAKEITDOUBLE",
      "serial": "short 船尾",
      "brand": "DOUBLE TROUBLE",
      "component": "/equipment_serial_components/5"
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to the string "/equipment_serials/18"
    And the JSON node "legacyId" should be equal to the number 313247
    And the JSON node "component.@id" should be equal to the string "/equipment_serial_components/5"
    And the JSON node "component.name" should be equal to the string "MANUAL"
    And the JSON node "model" should be equal to the string "MAKEITDOUBLE"
    And the JSON node "serial" should be equal to the string "short 船尾"
    And the JSON node "brand" should be equal to the string "DOUBLE TROUBLE"
    Then a new row has been inserted in the legacy table "service_serials"
    And the column "parent_id" from the "service_serials" legacy table has been inserted with integer 37456
    And the column "model" from the "service_serials" legacy table has been inserted with string "MAKEITDOUBLE"
    And the column "serial" from the "service_serials" legacy table has been inserted with string "short &#33337;&#23614;"
    And the column "brand" from the "service_serials" legacy table has been inserted with string "DOUBLE TROUBLE"
