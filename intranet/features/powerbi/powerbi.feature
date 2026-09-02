Feature: PowerBI
  Scenario: As an anonymous user, test that i'm not allowed to see power BI reports
    When I go to "/powerbi-reports"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/powerbi-reports/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a allowed user, test that i'm allowed to see power BI reports page
    Given I authenticate as "user-mis@tld.fr" with "P@ssw0rd15chars"
    When I go to "/powerbi-reports"
    Then the response status code should be 200
    And I should see "Power BI Reports"
    And I should see "Inventory Forecast"
    And I should not see an "a[href$='/powerbi-reports/add']" element
    And I should see an "a[href$='/powerbi-reports/1/show']" element
    And I should not see an "a[href$='/powerbi-reports/1/edit']" element

  Scenario: As an intranet user, test that i'm allowed to see AES power BI reports pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/powerbi-reports/aes/maintenance"
    Then the response status code should be 200
    When I go to "/powerbi-reports/aes/parts"
    Then the response status code should be 200
    When I go to "/powerbi-reports/aes/smw/sales"
    Then the response status code should be 200
    When I go to "/powerbi-reports/aes/smw/echotech"
    Then the response status code should be 200
    When I go to "/powerbi-reports/aes/smw/maintenance"
    Then the response status code should be 200
    When I go to "/powerbi-reports/aes/smw/finance"
    Then the response status code should be 200
    When I go to "/powerbi-reports/aes/smw/inventory"
    Then the response status code should be 200
    When I go to "/powerbi-reports/aes/smw/hr"
    Then the response status code should be 200
    When I go to "/powerbi-reports/aes/smw/qehs"
    Then the response status code should be 200
    When I go to "/powerbi-reports/aes/smw/purchasing"
    Then the response status code should be 200

  Scenario: As an allowed user, test that i'm allowed to see power BI reports page and admin actions
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/powerbi-reports"
    Then the response status code should be 200
    And I should see "Power BI Reports"
    And I should see "Inventory Forecast"
    And I should see an "a[href$='/powerbi-reports/add']" element
    And I should see an "a[href$='/powerbi-reports/1/show']" element
    And I should see an "a[href$='/powerbi-reports/1/edit']" element

  Scenario: As a allowed user, I can filter power BI reports page
    Given I authenticate as "user-mis@tld.fr" with "P@ssw0rd15chars"
    When I go to "/powerbi-reports"
    And I fill in the following:
      | filter_report[description][value] | forecast |
    And press "Search"
    Then I should see 1 "table.table tbody tr" elements
    And I should see "Inventory Forecast"
    And I should see "BI08"

  Scenario: As an allowed user, I can add a power BI report
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/powerbi-reports/add"
    And I fill in "report[title]" with "new title"
    And I fill in "report[description]" with "new description"
    And I fill in "report[category]" with "FINANCE"
    And I fill in "report[powerBiUuid]" with "ff5f39fd-11ad-40f6-9521-42bc83497fbc"
    And I press "submit"
    And I should see "Report created with success"
    And I should see "new title"
    And I should see "new description"

  Scenario: As an allowed user, I can edit a power BI report
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/powerbi-reports/6/edit"
    And I fill in "report[title]" with "title updated"
    And I fill in "report[description]" with "description updated"
    And I fill in "report[category]" with "ENGINEERING"
    And I fill in "report[powerBiUuid]" with "73db4597-4efd-4502-81b9-cdf009456288"
    And I press "submit"
    Then the response status code should be 200
    And I should see "Report updated with success"
    And I should see "title updated"

  Scenario: As an allowed user, I can delete a power BI report
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/powerbi-reports/7/delete"
    Then the response status code should be 200
    And I should see "Report deleted with success"
    And I should not see "Report to be deleted"

  Scenario: As a allowed user, test that i'm not allowed to create, edit or delete a power bi report
    Given I authenticate as "user-mis@tld.fr" with "P@ssw0rd15chars"
    When I go to "/powerbi-reports/add"
    Then the response status code should be 200
    Then I should not be on "/powerbi-reports/add"
    And I should see "You do not have permissions"
    Then I go to "/powerbi-reports/1/edit"
    Then the response status code should be 200
    Then I should not be on "/powerbi-reports/1/edit"
    And I should see "You do not have permissions"
    Then I go to "/powerbi-reports/1/delete"
    Then the response status code should be 200
    Then I should not be on "/powerbi-reports/1/delete"
    And I should see "You do not have permissions"

  Scenario: As a allowed user, test that i'm allowed to see SMW power BI reports page
    Given I authenticate as "user-mis@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/smw-meeting/6.1"
    Then the response status code should be 200
    And I should see "6.1.2 Raw Material Inventory Forecast"