Feature: The crab legacy pages should be redirected

  Scenario: The crab homepage should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/manufacturing/qa/dev.php?m[0]=crab"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/crabs"
    When I go to "/manufacturing/qa/dev.php?m[0]=crab&m[1]=view&id=114021"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/crabs/1/show"
    When I go to "/manufacturing/qa/dev.php?m[0]=crab&m[1]=view&m[2]=duplicate&id=114021"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/crabs/1/duplicate"
    When I go to "/manufacturing/qa/dev.php?m[0]=crab&m[1]=view&id=10000000"
    Then the response status code should be 404
    When I go to "/manufacturing/qa/crab/crab_admin.php"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/crabs"
    When I go to "/manufacturing/qa/dev.php?m[0]=crab&m[1]=reports"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/crabs/reports"
    When I go to "/manufacturing/qa/dev.php?m[0]=crab&m[1]=graph"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/crabs/reports"
    When I go to "/manufacturing/qa/dev.php?m[0]=crab&m[1]=report"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/crabs/reports"
    When I go to "/manufacturing/qa/dev.php?m[0]=crab&m[1]=fullReport"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/crabs/reports"
    When I go to "/manufacturing/qa/dev.php?m[0]=crab&m[1]=listing"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/crabs"
    When I go to "/manufacturing/qa/dev.php?m[0]=crab&m[1]=form"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/crabs"
    When I go to "/manufacturing/qa/dev.php?m[0]=crab&m[1]=form&m[2]=newCRABPDI"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/crabs/add"
    When I go to "/manufacturing/qa/dev.php?m[0]=crab&m[1]=form&m[2]=newCRAB"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/crabs/add"
    When I go to "/manufacturing/qa/dev.php?m[0]=crab&m[1]=form&m[2]=newCRAB1"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/crabs/add"
    When I go to "/manufacturing/qa/dev.php?m[0]=crab&m[1]=form&m[2]=byNum"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/crabs"
    When I go to "/manufacturing/qa/dev.php?m[0]=crab&m[1]=form&m[2]=byNCR"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/crabs"
    When I go to "/manufacturing/qa/dev.php?m[0]=crab&m[1]=form&m[2]=search"
    Then I should not be on a legacy page
    And I should be on the exact url "/quality/crabs"
