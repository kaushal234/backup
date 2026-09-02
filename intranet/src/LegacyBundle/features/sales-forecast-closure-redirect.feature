Feature: The sales forecast closure legacy pages should be redirected

  Scenario: The pages should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=fcr"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/forecast-closures"
    When I go to "/sales_service/sales.php?m[0]=fcr&m[1]=search&m[2]=searchFCR"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/forecast-closures"
    When I go to "/sales_service/sales.php?m[0]=fcr&m[1]=lookup"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/forecast-closures"
    When I go to "/sales_service/sales.php?m[0]=fcr&m[1]=search&m[2]=searchCPR"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/competitor-pricings"
    When I go to "/sales_service/sales.php?m[0]=fcr&m[1]=view&id=6125112"
    Then the response status code should be 404
    When I go to "/sales_service/sales.php?m[0]=fcr&m[1]=view&id=6112"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/forecast-closures/1/show"
    When I go to "/sales_service/sales.php?m[0]=fcr&m[1]=closeSFR&sfrid=16218"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/1/show"
    When I go to "/sales_service/sales.php?m[0]=fcr&m[1]=closeSFR&sfrid=1621224568"
    Then the response status code should be 404
    When I go to "/sales_service/sales.php?m[0]=fcr&m[1]=closeSFR&m[2]=setCPR&sfrid=16218"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/sales-forecasts/1/add-cpr"
