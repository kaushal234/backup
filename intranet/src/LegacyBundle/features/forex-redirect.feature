Feature: The forex legacy pages should be redirected

  Scenario: The forex should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/finance.php?m[0]=forex"
    Then I should not be on a legacy page
    And I should be on the exact url "/finance/forex/"
    When I go to "/finance/finance.php?m[0]=forex&m[1]=form&m[2]=addRates"
    Then I should not be on a legacy page
    And I should be on the exact url "/finance/forex/add"
    When I go to "/finance/finance.php?m[0]=forex&m[1]=reports&m[2]=csv"
    Then I should not be on a legacy page
    And I should be on the exact url "/finance/forex/search?download="
    When I go to "/finance/forex/forex_admin.php"
    Then I should not be on a legacy page
    And I should be on the exact url "/finance/forex/search"
