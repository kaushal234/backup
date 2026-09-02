Feature: The tracking legacy pages should be redirected

  Scenario: The home be redirected to the main dashboard
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/parts.php?m[0]=dino_trno"
    Then I should not be on a legacy page
    And I should be on the homepage
    When I go to "/parts/parts.php?m[0]=dino_trno&d=yesterday"
    Then I should not be on a legacy page
    And I should be on the homepage
    When I go to "/parts/parts.php?m[0]=dino_trno&m[1]=add&erp=420&dino=40335"
    Then I should not be on a legacy page
    And I should be on the homepage
    When I go to "/parts/dino_trno/dino_trno_admin.php"
    Then I should not be on a legacy page
    And I should be on the homepage
