Feature: The contacts legacy pages should be redirected

  Scenario: The dashboards should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=extranet"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/contacts"
    When I go to "/sales_service/sales.php?m[0]=extranet&m[1]=byNumber"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/contacts"
    When I go to "/sales_service/sales.php?m[0]=extranet&m[1]=listing"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/contacts"
    When I go to "/sales_service/sales.php?m[0]=extranet&m[1]=reports"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/contacts"

  Scenario: The detail pages should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=extranet&m[1]=view&id=7200"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/contacts/200/show"
    When I go to "/sales_service/sales.php?m[0]=extranet&m[1]=view&m[2]=log&id=7200"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/contacts/200/show"
    When I go to "/sales_service/sales.php?m[0]=extranet&m[1]=view&m[2]=log&id=7200548"
    Then the response status code should be 404

  Scenario: The detail pages should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=extranet&m[1]=view&m[2]=edit&id=7200"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/contacts/200/edit"
    When I go to "/sales_service/sales.php?m[0]=extranet&m[1]=view&m[2]=roles&id=7200"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/contacts/200/crt_roles"
    When I go to "/sales_service/sales.php?m[0]=extranet&m[1]=view&m[2]=roles&m[3]=add&id=7200"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/contacts/200/crt_roles/add"
    When I go to "/sales_service/sales.php?m[0]=extranet&m[1]=view&m[2]=email&id=7200"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/contacts/200/confirmation_mail"
