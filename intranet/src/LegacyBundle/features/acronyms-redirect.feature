Feature: The acronyms legacy pages should be redirected

  Scenario: The acronyms should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/corporate/index.php?m[0]=agr"
    Then I should not be on a legacy page
    And I should be on the exact url "/acronyms"
    When I go to "/corporate/agr/agr_admin.php"
    Then I should not be on a legacy page
    And I should be on the exact url "/acronyms"
    When I go to "/corporate/index.php?m[0]=agr&m[1]=form&m[2]=new"
    Then I should not be on a legacy page
    And I should be on the exact url "/acronyms/add"
    When I go to "/corporate/index.php?m[0]=agr&m[1]=listing&m[2]=search"
    Then I should not be on a legacy page
    And I should be on the exact url "/acronyms/search"
    When I go to "/corporate/index.php?m[0]=agr&m[1]=view&id=99"
    Then I should not be on a legacy page
    And I should be on the exact url "/acronyms/1"
    When I go to "/corporate/index.php?m[0]=agr&m[1]=view&id=654562"
    Then the response status code should be 404
