Feature: The modules legacy pages should be redirected

  Scenario: The modules should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/mis.php?m[0]=module"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/modules"
    When I go to "/mis/mis.php?m[0]=module&id=60"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/modules/1/show"
    When I go to "/mis/mis.php?m[0]=module&id=605256"
    Then the response status code should be 404
    When I go to "/module/module_admin.php?mode=record_view&form_type=main_tpl&id=60"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/groups/1/show"
    When I go to "/module/module_admin.php?mode=del&form_type=main_tpl&id=60"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/groups/1/show"
    When I go to "/module/module_admin.php?mode=form_edit&form_type=main_tpl&id=60"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/groups/1/edit"
    When I go to "/mis/mis.php?m[0]=module&m[1]=view&m[2]=edit&id=60"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/modules/1/edit"
