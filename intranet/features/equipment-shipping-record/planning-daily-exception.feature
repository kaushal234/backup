Feature: Planning daily Exception

  Scenario: As an allowed user, test that i can edit a planning daily exception
    Given I authenticate as "user-psm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/planning_daily_exception/1/edit"
    Then the response status code should be 200
    Then I fill in "planning_daily_exception[comment]" with "Pas de gros IVECO, ni de petit, mais il fait beau"
    And press "submit"
    Then the response status code should be 200
    And I should be on "sales/equipment_shipping_records/planning?smw_filter[location]=/locations/30"
    And I should see "Planning daily exception has been successfully saved"
    And I should see "Pas de gros IVECO, ni de petit, mais il fait beau"

  Scenario: As an allowed user, test that i can delete a planning daily exception
    Given I authenticate as "user-psm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/planning_daily_exception/1/delete"
    Then the response status code should be 200
    And I should be on "sales/equipment_shipping_records/planning?smw_filter[location]=/locations/30"
    And I should see "Planning daily exception deleted"

  Scenario: As an allowed user, test that i'm allowed to add an planning exception
    Given I authenticate as "user-psm@tld.fr" with "P@ssw0rd15chars"
    When I go to "sales/equipment_shipping_records/planning?smw_filter[location]=/locations/30"
    Then the response status code should be 200
    Then I fill in "planning_daily_exception[comment]" with "Piscine"
    And I fill in "planning_daily_exception[date]" with "06/01/2099"
    And I press "planning-daily-exception[submit]"
    Then the response status code should be 200
    And I should see "Piscine"
    And I should see "Planning daily exception has been successfully saved"

  Scenario: As an allowed user, test that i'm not allowed to add an planning exception on a same date
    Given I authenticate as "user-psm@tld.fr" with "P@ssw0rd15chars"
    When I go to "sales/equipment_shipping_records/planning?smw_filter[location]=/locations/30"
    Then the response status code should be 200
    Then I fill in "planning_daily_exception[comment]" with "Bamos a la playa"
    And I fill in "planning_daily_exception[date]" with "06/01/2099"
    And I press "planning-daily-exception[submit]"
    Then the response status code should be 200
    And I should see "An exception already exists for this factory and date"
    And I should see "Piscine"
    And I should not see "Bamos a la playa"
