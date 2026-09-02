Feature: The equipment shipping record legacy pages should be redirected

  Scenario: The equipment shipping record should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=esr"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/equipment_shipping_records"
    When I go to "/sales_service/esr/esr_admin.php"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/equipment_shipping_records"
    When I go to "/sales_service/sales.php?m[0]=esr&m[1]=form&m[2]=add"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/equipment_shipping_records/add"
    When I go to "/sales_service/sales.php?m[0]=esr&m[1]=listing&m[2]=search"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/equipment_shipping_records"
    When I go to "/sales_service/sales.php?m[0]=esr&m[1]=view&id=717"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/equipment_shipping_records/1/show"
    When I go to "/sales_service/sales.php?m[0]=esr&m[1]=view&m[2]=edit&id=717"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/equipment_shipping_records/1/edit"
    When I go to "/sales_service/sales.php?m[0]=esr&m[1]=view&id=65456201"
    Then the response status code should be 404
