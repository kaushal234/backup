Feature: The sales orders legacy pages should be redirected

  Scenario: The dashboards should be redirected
    Given I authenticate as "user-sa@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=sor"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/orders"
    When I go to "/sales_service/sales.php?m[0]=sor&m[1]=form&m[2]=byNum"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/orders"
    When I go to "/sales_service/sales.php?m[0]=sor&m[1]=listing&m[2]=search"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/orders"

  Scenario: The transfer should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=sor&m[1]=form&m[2]=transfer"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/orders"
    And I should see "No quote provided"

  Scenario: The sor creation should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=sor&m[1]=form&m[2]=newSOR1"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/orders/add"
    When I go to "/sales_service/sales.php?m[0]=sor&m[1]=form&m[2]=newSOR"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/orders/add"

  Scenario: The detail pages should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=sor&m[1]=view&id=5265485"
    Then the response status code should be 404
    When I go to "/sales_service/sales.php?m[0]=sor&m[1]=view&id=18894"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/orders/1/show"
    When I go to "/sales_service/sales.php?m[0]=sor&m[1]=view&m[2]=xmlSource&id=18894"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/orders/1/show"
    When I go to "/sales_service/sales.php?m[0]=sor&m[1]=view&m[2]=changeStatus&id=18894"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/orders/1/show"
    When I go to "/sales_service/sales.php?m[0]=sor&m[1]=view&m[2]=log&id=18894"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/orders/1/logs"
    When I go to "/sales_service/sales.php?m[0]=sor&m[1]=view&m[2]=edit&id=18894"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/orders/1/edit"
    When I go to "/sales_service/sales.php?m[0]=sor&m[1]=view&m[2]=edit2&id=18894"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/orders/1/edit"
    When I go to "/sales_service/sales.php?m[0]=sor&m[1]=view&m[2]=dup&id=18894"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/orders/1/duplicate"
    When I go to "/sales_service/sales.php?m[0]=sor&m[1]=view&m[2]=tasks&id=18894"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/orders/1/tasks"
    When I go to "/sales_service/sales.php?m[0]=sor&m[1]=view&m[2]=files&id=18894"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/orders/1/files"
