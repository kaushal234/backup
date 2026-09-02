Feature: The sales competitors legacy pages should be redirected

  Scenario: The COR should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=cor"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/competitors"
    When I go to "/sales_service/sales.php?m[0]=cor&m[1]=byNumber"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/competitors"
    When I go to "/sales_service/sales.php?m[0]=cor&m[1]=search"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/competitors"
    When I go to "/sales_service/sales.php?m[0]=cor&m[1]=byType&type=Type%20English"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/competitors?productTypes%5B0%5D=1"
    When I go to "/sales_service/sales.php?m[0]=cor&m[1]=byType&type=DOESNOTEXIST"
    Then the response status code should be 404
    When I go to "/sales_service/sales.php?m[0]=cor&m[1]=view&id=215"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/competitors/1/show"
    When I go to "/sales_service/sales.php?m[0]=cor&m[1]=view&id=2151145"
    Then the response status code should be 404
    When I go to "/sales_service/sales.php?m[0]=cor&m[1]=view&m[2]=files&id=215"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/competitors/1/files"
    When I go to "/common/index.php?m[0]=files&m[1]=form&m[2]=newFile&module=COR&parent_id=215"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/competitors/1/files"
    When I go to "/common/index.php?m[0]=files&m[1]=form&m[2]=newFile&module=COR&parent_id=5212215"
    Then the response status code should be 404
