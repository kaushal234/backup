Feature: Warehouse tasks mappings

  Scenario: As an anonymous user, test that i'm not allowed to see task mappings pages
    When I go to "/materials/warehouse/monthly-activities"
    Then I should be on "/login"

  @javascript
  Scenario: As a basic user, test that i'm allowed to see KPI page for all BU
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/materials/warehouse/monthly-activities"
    And I wait until I see "WHSE"
    And I should not see an "#activityContainer" element
    And I should not see an "#productivityContainer" element
    And I should not see an "#fullTimeEquivalentContainer" element
    And I should not see an ".aggregate" element
    And I should be on "/materials/warehouse/monthly-activities"
    And press "FILTER"
    And I wait for "#activityContainer" element
    And I wait for "#productivityContainer" element
    And I should not see an "#fullTimeEquivalentContainer" element
    And I should see 5 ".aggregate" elements
    And I should be on "/materials/warehouse/monthly-activities"
    Then I select "/locations/33" from "location"
    And press "FILTER"
    And I wait for "#activityContainer" element
    And I wait for "#productivityContainer" element
    And I wait for "#fullTimeEquivalentContainer" element
    And I wait for ".aggregate" element
    And I should be on "/materials/warehouse/monthly-activities"
