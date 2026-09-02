Feature: The ranking legacy pages should be redirected

  Scenario: The ranking pages should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/manufacturing/pur/dev.php?m[0]=vendors"
    Then I should not be on a legacy page
    And I should be on the exact url "/purchasing/supplier-rankings/"