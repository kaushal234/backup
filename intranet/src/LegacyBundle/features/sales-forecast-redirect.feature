Feature: The sales forecast legacy pages should be redirected

  Scenario: The home be redirected to the main dashboard
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=sfr"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/dashboard"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=home"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/dashboard"

  Scenario: The links on old matrix reports should be redirected to the main dashboard
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=listing&m[2]=mySFRByCustomer"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/dashboard"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=listing&m[2]=byOpenStatusERPASM"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/dashboard"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=listing&m[2]=byOpenStatusDelinquantERPASM"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/dashboard"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=listing&m[2]=byRecentlyClosedERPASM"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/dashboard"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=listing&m[2]=bySubordinates"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/dashboard"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=listing&m[2]=byAllCustomerID&x=4074"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts?buyer=/sales/customers/1"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=listing&m[2]=byAllCustomerID&x=52451562"
    Then the response status code should be 404

  Scenario: Specific ASM dashboards should be redirected to the ASM dashboard
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=home&m[2]=asmDashboard"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/dashboard-asm"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=home&m[2]=selectASM"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/dashboard-asm"
    When I go to "/sales_service/sales.php?m[0]=sfr"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/dashboard-asm"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=home"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/dashboard-asm"

  Scenario: The edit page should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=listing&m[2]=edit"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/quick-edit"

  Scenario: The search should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=listing&m[2]=search"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/gantt?search=1"

  Scenario: The dashboard should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=reports"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=listing"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=form"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=form&m[2]=byNum"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts"

  Scenario: The newSFR should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=form&m[2]=newSFR"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/add"

  Scenario: The view should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=view&id=16251218"
    Then the response status code should be 404
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=view&id=16218"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/1/show"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=view&m[2]=tasks&id=16218"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/1/show"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=view&m[2]=files&id=16218"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/1/show"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=view&m[2]=log&id=16218"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/1/show"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=view&m[2]=linked&id=16218"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/1/show"

  Scenario: The editASM, PSM should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=view&m[2]=editASM&id=16227"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/10/edit"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=view&m[2]=editPSM&id=16227"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/10/edit"

  Scenario: The view should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=sfr&m[1]=view&m[2]=notExist&id=16218"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/add"
