Feature: The manufacturing margins legacy pages should be redirected

  Scenario: The manufacturing margin should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/finance.php?m[0]=mfg_margins"
    Then I should not be on a legacy page
    And I should be on the exact url "/finance/manufacturing-margins"
    When I go to "/finance/finance.php?m[0]=mfg_margins&m[1]=byNum"
    Then I should not be on a legacy page
    And I should be on the exact url "/finance/manufacturing-margins"
    When I go to "/finance/finance.php?m[0]=mfg_margins&m[1]=reports"
    Then I should not be on a legacy page
    And I should be on the exact url "/finance/manufacturing-margins/reports"
    When I go to "/finance/finance.php?m[0]=mfg_margins&m[1]=addmultiple"
    Then I should not be on a legacy page
    And I should be on the exact url "/finance/manufacturing-margins/upload"
    When I go to "/finance/finance.php?m[0]=mfg_margins&m[1]=add"
    Then I should not be on a legacy page
    And I should be on the exact url "/finance/manufacturing-margins/add"

  Scenario: The manufacturing margin view pages should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/finance.php?m[0]=mfg_margins&m[1]=view&id=2450"
    Then I should not be on a legacy page
    And I should be on the exact url "/finance/manufacturing-margins/1/show"
    When I go to "/finance/finance.php?m[0]=mfg_margins&m[1]=view&id=5296544"
    Then the response status code should be 404
    When I go to "/finance/finance.php?m[0]=mfg_margins&m[1]=view&m[2]=edit&id=2450"
    Then I should not be on a legacy page
    And I should be on the exact url "/finance/manufacturing-margins/1/edit"
    When I go to "/finance/finance.php?m[0]=mfg_margins&m[1]=view&m[2]=log&id=2450"
    Then I should not be on a legacy page
    And I should be on the exact url "/finance/manufacturing-margins/1/show"
