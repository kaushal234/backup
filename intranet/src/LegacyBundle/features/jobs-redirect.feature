Feature: The jobs legacy pages should be redirected

  Scenario: The jobs should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/index.php?m[0]=jobs"
    Then I should not be on a legacy page
    And I should be on the exact url "/human-resources/jobs"
