Feature: The parts dashboard legacy pages should be redirected

  Scenario: The parts dashboard should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/parts.php?m[0]=inv&m[1]=view&id=5033251-010"
    Then I should not be on a legacy page
    And I should be on the exact url "/parts/dashboard/5033251-010"
    When I go to "/parts/parts.php?m[0]=inv&m[1]=view&id=1pt"
    And I should see "No results"

  @authentication
  Scenario: Shortage report URL in legacy is redirected to the Symfony version
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/parts.php?m[0]=inv&m[1]=view&id=5033251-010"
    Then I should be on homepage
    And I should see "You do not have permissions"