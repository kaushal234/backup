Feature: Planning Daily

  Scenario: As a basic user, test that i'm allowed to see planning daily limit
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/equipment_shipping_records/planning?smw_filter[location]=%2Flocations%2F30&submit=Search"
    Then the response status code should be 200
    And I should see "Planning"
    And I should see "PDI2025"
    And I should see an "span[title$='Pickup planned more than 30 days after Green Tag. Unit will automatically switch to Yellow Tag before shipment.']" element
