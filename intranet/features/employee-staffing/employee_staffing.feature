Feature: Employee Staffing Report

  Scenario: As an anonymous user, test that i'm not allowed to see the report
    When I go to "/human-resources/employee-staffing/report"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm not allowed to see the report
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/employee-staffing/report"
    Then I should not be on "/human-resources/employee-staffing/report"
    And I should see "You do not have permissions"

  Scenario: As a super user, test that i'm allowed to see the report
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/employee-staffing/report?entity=/divisions/2"
    And the response status code should be 200
    And The module should be "ESM"
    And I should see "Business units included in this report"
    And I should see "(-3 for business_unit_2) Roger is very very sick"
    And I should not see "specific comment to make sure that snapshot data is loaded"
    And I should see an "a[href$='/human-resources/employee-staffing/report?entity=/business_units/1']" element

  Scenario: As a super user, test that i'm allowed to see the report with snapshot on division
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/employee-staffing/report?entity=/sub_divisions/1&snapshot=2021-02-24"
    And the response status code should be 200
    And The module should be "ESM"
    And I should see "(+1 for SAY_MY_NAME) Bob is working like a dog - specific comment to make sure that snapshot data is loaded"
    And I should see an "a[href$='/human-resources/employee-staffing/report?entity=/business_units/2']" element

  Scenario: As a super user, test that i'm allowed to see the report and complete the form
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/employee-staffing/report?entity=/business_units/16"
    And the response status code should be 200
    And The module should be "ESM"
    And I fill in "position_classification_report_batch[positionClassifications][2][correction]" with "-1"
    And I fill in "position_classification_report_batch[positionClassifications][2][comment]" with "Philippe was in the toilets"
    And I fill in "position_classification_report_batch[positionClassifications][2][budget]" with "45"
    And I fill in "position_classification_report_batch[positionClassifications][2][reforecast]" with "37"
    And press "position_classification_report_batch[submit]"
    Then the response status code should be 200
    And I should be on "/human-resources/employee-staffing/report?entity=/business_units/16"
    And I should see "Philippe was in the toilets"

  Scenario: As a basic user, test that i'm not allowed to see the report
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/employee-staffing/regional-results-review"
    Then I should not be on "/human-resources/employee-staffing/regional-results-review"
    And I should see "You do not have permissions"

  Scenario: As a super user, test that i'm allowed to see the rrr view
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/employee-staffing/regional-results-review?entity=/divisions/2"
    And the response status code should be 200
    And The module should be "ESM"
    And I should not see "Apprentices"
    And I should not see "Temps & consultants"
    And I should see "Indirect Totals"
    And I should see "Direct Totals"
