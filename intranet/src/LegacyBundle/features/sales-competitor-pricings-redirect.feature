Feature: The sales competitor pricing legacy pages should be redirected

  Scenario: The legacy pages should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=cpr"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/competitor-pricings"
    When I go to "/sales_service/sales.php?m[0]=cpr&m[1]=byNum"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/competitor-pricings"
    When I go to "/sales_service/sales.php?m[0]=cpr&m[1]=search"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/competitor-pricings"
    When I go to "/sales_service/sales.php?m[0]=cpr&m[1]=view&id=1219"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/competitor-pricings/1/show"
    When I go to "/sales_service/sales.php?m[0]=cpr&m[1]=view&id=12551219"
    Then the response status code should be 404
