Feature: Manufacturing Margin

  Scenario: As an anonymous user, test that i'm not allowed to see manufacturing margin pages
    When I go to "/finance/manufacturing-margins"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/finance/manufacturing-margins/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a superuser, test that i'm allowed to see manufacturing margin pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/manufacturing-margins"
    Then the response status code should be 200
    When I go to "/finance/manufacturing-margins/1/show"
    Then the response status code should be 200
    And The module should be "RRR"
    And I should see "Record Details"
    And I should see an "a[href$='/finance/manufacturing-margins/1/edit']" element
    And I should see an "a[href$='/finance/manufacturing-margins/1/delete']" element
    When I go to "/finance/manufacturing-margins/reports"
    Then the response status code should be 200
    And I should see "Manufacturing Margin Report"

  Scenario: As a superuser, test that i can download report
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/manufacturing-margins/download-by-er"
    Then the response status code should be 200
    And I fill in the following:
     | form[from] | 01/01/2022, 12:01 AM |
     | form[to] | 02/28/2022, 12:01 AM |
    And I select "location_factory" from "form[factory][]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/finance/manufacturing-margins/download-by-er"

  Scenario: As a superuser, test that i can download report
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/manufacturing-margins/download-by-finance-family"
    Then the response status code should be 200
    And I fill in the following:
      | form[from] | 01/01/2022 |
      | form[to] | 02/28/2022 |
    And I select "/locations/29" from "form[factory]"
    And press "submit"
    Then the response status code should be 200
    Then I should see response headers "content-type" with "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    Then I should see response headers "content-disposition" with 'inline; filename=restitution_by_finance_family.xlsx'

  Scenario: As a superuser, test that i can edit a manufacturing margin
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/manufacturing-margins/2/edit"
    Then the response status code should be 200
    When I fill in the following:
      | form[comment] | Please |
    And press "submit"
    Then the response status code should be 200
    And I should be on "/finance/manufacturing-margins/2/show"
    And I should see "Please"

  Scenario: As a superuser, test that i can add a manufacturing margin
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/manufacturing-margins/add"
    Then the response status code should be 200
    When I fill in the following:
      | form[actualHours] | 150  |
      | form[standardHours] | 150  |
      | form[actualLabourCost] | 1500  |
      | form[standardLabourCost] | 1425  |
      | form[standardMaterialCost] | 1578  |
      | form[actualMaterialCost] | 1526  |
      | form[standardOtherMaterialCost] | 36524  |
      | form[actualOtherMaterialCost] | 63254  |
      | form[standardOtherDirectCost] | 1452  |
      | form[actualOtherDirectCost] | 1584  |
      | form[optionConfigurationParameterHours] | 54  |
      | form[factoryRevenue] | 215847  |
      | form[comment] | Please add  |
    And I select "/equipment_records/14" from "form[equipmentRecord]"
    And I select "EUR" from "form[currency]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/finance/manufacturing-margins/4/show"
    And I should see "Please add"

  Scenario: A superuser user can delete manufacturing margin
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/manufacturing-margins/2/show"
    Then the response status code should be 200
    And I should see an "a[href$='/finance/manufacturing-margins/2/delete']" element
    When I go to "/finance/manufacturing-margins/2/delete"
    Then the response status code should be 200
    And I should be on "/finance/manufacturing-margins"
    And I should see "This record has been successfully deleted"
