Feature: The supplier corrective action request legacy pages should be redirected

  Scenario: The supplier corrective action request homepage should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/manufacturing/qa/dev.php?m[0]=scar"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/supplier-corrective-action-requests"
    When I go to "/manufacturing/qa/dev.php?m[0]=scar&m[1]=view&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/supplier-corrective-action-requests/1/show"
    When I go to "/manufacturing/qa/dev.php?m[0]=scar&m[1]=view&id=125700"
    Then the response status code should be 404
    When I go to "/manufacturing/qa/scar/scar_admin.php"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/supplier-corrective-action-requests"
    When I go to "/manufacturing/qa/dev.php?m[0]=scar&m[1]=view&m[2]=edit&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/supplier-corrective-action-requests/1/edit"
