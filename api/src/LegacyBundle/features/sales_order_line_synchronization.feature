Feature: Test sales order line double write API

  Scenario: Create a PDI on ER should set 'inspection' to 'true' on its legacy SOL
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/pre_delivery_inspections" with body:
    """
      {
        "equipmentRecord": "equipment_records/21"
      }
    """
    Then the response status code should be 201
    And the column "conf_cis" from the "sor_lines" legacy table has been updated with string "Y"
