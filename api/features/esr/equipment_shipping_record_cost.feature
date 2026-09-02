Feature: Test Equipment Shipping Record Cost API

  Scenario: I cannot create an ESRC as user-basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/equipment_shipping_record_costs" with body:
    """
    {
      "costDate": "2023-08-25T00:00:00-0400",
      "type": "Customs duties",
      "description": "remboursez moi!!!",
      "currency": "/finance/currencies/1",
      "price": 1200000,
      "equipmentShippingRecord": "/sales/equipment_shipping_records/1"
    }
    """
    Then the response status code should be 403

  Scenario: I can create an ESR Cost as allowed user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/equipment_shipping_record_costs" with body:
    """
    {
      "costDate": "2023-08-25T00:00:00-0400",
      "type": "Customs duties",
      "description": "remboursez moi!!!",
      "currency": "/finance/currencies/1",
      "price": 1200000,
      "equipmentShippingRecord": "/sales/equipment_shipping_records/1"
    }
    """
    Then the response status code should be 201
    And the JSON node "costDate" should be equal to the string "2023-08-25T00:00:00-04:00"
    And the JSON node "type" should be equal to the string "Customs duties"
    And the JSON node "description" should be equal to the string "remboursez moi!!!"
    And the JSON node "currency" should be equal to the string "/finance/currencies/1"
    And the JSON node "price" should be equal to 1200000

  Scenario: I can edit an ESR Cost as allowed user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/equipment_shipping_record_costs/1" with body:
    """
    {
        "costDate": "2024-08-25T00:00:00-0400",
        "type": "Others",
        "description": "Donnes la moula tarba",
        "currency": "/finance/currencies/2",
        "price": 3
    }
    """
    Then the response status code should be 200
    And the JSON node "costDate" should be equal to the string "2024-08-25T00:00:00-04:00"
    And the JSON node "type" should be equal to the string "Others"
    And the JSON node "description" should be equal to the string "Donnes la moula tarba"
    And the JSON node "currency" should be equal to the string "/finance/currencies/2"
    And the JSON node "price" should be equal to 3

  Scenario: I can delete an ESR Cost as allowed user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/equipment_shipping_record_costs/1"
    Then the response status code should be 204
