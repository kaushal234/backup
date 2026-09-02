Feature: The customer relationship team legacy pages should be redirected

  Scenario: The new should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=crt&m[1]=forms&m[2]=new"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customer-relationship-teams/add"
    When I go to "/sales_service/sales.php?m[0]=crt&m[1]=forms&m[2]=new2"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customer-relationship-teams/add"

  Scenario: The search should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=crt&m[1]=forms&m[2]=search"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customer-relationship-teams"
    When I go to "/sales_service/sales.php?m[0]=crt&m[1]=forms&m[2]=byNum"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customer-relationship-teams"
    When I go to "/sales_service/sales.php?m[0]=crt&m[1]=reports"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customer-relationship-teams"
    When I go to "/sales_service/sales.php?m[0]=crt&m[1]=lists"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customer-relationship-teams"

  Scenario: The edit should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=crt&m[1]=view&m[2]=edit&id=4390"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customer-relationship-teams/6/edit"
    When I go to "/sales_service/sales.php?m[0]=crt&m[1]=view&m[2]=task&id=4390"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customer-relationship-teams/6/edit"
    When I go to "/sales_service/sales.php?m[0]=crt&m[1]=view&m[2]=edit2&id=4390"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customer-relationship-teams/6/edit"

  Scenario: The show should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=crt&m[1]=view&id=4390"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customer-relationship-teams/6/show"
    When I go to "/sales_service/sales.php?m[0]=crt&m[1]=view&id=439014"
    Then the response status code should be 404

  Scenario: The duplicate should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=crt&m[1]=view&m[2]=duplicate&id=4390"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customer-relationship-teams/6/duplicate"

  Scenario: The log should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=crt&m[1]=view&m[2]=log&id=4390"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customer-relationship-teams/6/show"

  Scenario: The delete should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=crt&m[1]=view&m[2]=delete&id=4397"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/customer-relationship-teams"
    And I should see "The CRT has been deleted successfully"
