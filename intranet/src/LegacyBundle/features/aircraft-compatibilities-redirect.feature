Feature: The aircraft compatibility legacy pages should be redirected

  Scenario: The AC should be redirected
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=gse_aircraft_data"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/aircraft-compatibilities"
    When I go to "/product_support/nto/nto_admin.php"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/aircraft-compatibilities"