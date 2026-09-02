Feature: The group legacy pages should be redirected

  Scenario: The group should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/mis.php?m[0]=grdesc"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/groups"
    When I go to "/mis/grdesc/grdesc_admin.php?mode=record_view&id=165"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/groups/1/show"
    When I go to "/mis/grdesc/grdesc_admin.php?mode=record_view&id=6285"
    Then the response status code should be 404
    When I go to "/mis/grdesc/grdesc_admin.php?mode=form_add"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/groups/add"
    When I go to "/mis/grdesc/grdesc_admin.php?mode=form_edit&id=165"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/groups/1/edit"
    When I go to "/mis/grdesc/grdesc_admin.php?mode=duplicate"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/groups/add"
    When I go to "/mis/grdesc/grdesc_admin.php?mode=del&id=164"
    Then I should not be on a legacy page
    And I should be on the exact url "/mis/groups/94/show"
    And I should see "Cannot delete this group"
