Feature: The non conformity legacy pages should be redirected

  Scenario: The non conformity homepage should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/manufacturing/qa/dev.php?m[0]=ncr"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/non-conformities"
    When I go to "/manufacturing/qa/dev.php?m[0]=ncr&m[1]=view&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/non-conformities/1/show"
    When I go to "/manufacturing/qa/dev.php?m[0]=ncr&m[1]=view&id=1000000"
    Then the response status code should be 404
    When I go to "/manufacturing/qa/ncr/ncr_admin.php"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/non-conformities"
    When I go to "/manufacturing/qa/dev.php?m[0]=ncr&m[1]=view&m[2]=edit&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/non-conformities/1/edit"
    When I go to "/manufacturing/qa/dev.php?m[0]=ncr&m[1]=reports"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/non-conformities/report"
