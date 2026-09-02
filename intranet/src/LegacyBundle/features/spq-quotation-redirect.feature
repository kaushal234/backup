Feature: The spq quotation legacy pages should be redirected

  Scenario: The spq quotation should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/parts.php?m[0]=spq&m[1]=form&m[2]=create"
    Then I should not be on a legacy page
    And I should be on the exact url "/parts/spq"
    When I go to "/parts/parts.php?m[0]=spq&m[1]=form&m[2]=create2"
    Then I should not be on a legacy page
    And I should be on the exact url "/parts/spq"
