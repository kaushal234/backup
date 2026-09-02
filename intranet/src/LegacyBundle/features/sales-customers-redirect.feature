Feature: The sales customers legacy pages should be redirected

  Scenario: The create customers should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=approval"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers/add"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=approval&m[2]=new"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers/add"

  Scenario: The dashboards should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=reports"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=byType"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=cleanup"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=list"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=last_seq"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=forms"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=delete&id=4074"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers"

  Scenario: The contact should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=contact&id=4074"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers/1/xu_linked"

  Scenario: The edit pages should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=edit&id=4074"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers/1/edit"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=editcrt&id=4074"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customer-relationship-teams"

  Scenario: The detail pages should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=contacts&id=4076524554"
    Then the response status code should be 404
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=contacts&id=4074"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers/1/show"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=hierarchy&id=4074"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers/1/show"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=crt&id=4074"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers/1/show"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=log&id=4074"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers/1/show"

  Scenario: The detail pages should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=inv&id=4074"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers/1/invoices"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=so&id=4074"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers/1/sales_orders"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=ps&id=4074"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers/1/packing_slips"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=email&id=4074"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers/1/email"
    When I go to "/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=zip&id=4074"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customers/1/zip"
