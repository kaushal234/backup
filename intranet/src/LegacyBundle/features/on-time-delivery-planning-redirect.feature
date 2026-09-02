Feature: The ODP legacy pages should be redirected

  Scenario: The odp homepage should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/product_support/index.ps.php?m[0]=odp"
    Then I should not be on a legacy page
    And I should be on the exact url "/support/on_time_delivery_planning"
    When I go to "/product_support/index.ps.php?m[0]=odp&m[1]=matrix"
    Then I should not be on a legacy page
    And I should be on the exact url "/support/on_time_delivery_planning"
    When I go to "/product_support/index.ps.php?m[0]=odp&m[1]=listing"
    Then I should not be on a legacy page
    And I should be on the exact url "/support/on_time_delivery_planning/show"
    When I go to "/product_support/index.ps.php?m[0]=odp&m[1]=reports"
    Then I should not be on a legacy page
    And I should be on the exact url "/support/on_time_delivery_planning/show"
    When I go to "/product_support/index.ps.php?m[0]=odp&m[1]=charts"
    Then I should not be on a legacy page
    And I should be on the exact url "/support/on_time_delivery_planning/show"
