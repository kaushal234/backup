Feature: The sales mim legacy pages should be redirected

  Scenario: The MIM should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=mim"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligences"
    When I go to "/sales_service/sales.php?m[0]=mim&m[1]=byNum"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligences"
    When I go to "/sales_service/sales.php?m[0]=mim&m[1]=list"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligences"
    When I go to "/sales_service/sales.php?m[0]=mim&m[1]=reports"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligences"
    When I go to "/sales_service/sales.php?m[0]=mim&m[1]=matrix"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligences"

  Scenario: The MIM add page should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=mim&m[1]=add"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligences/add"

  Scenario: The MIM not pages should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=mim&m[1]=not"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligence-subscriptions"
    When I go to "/sales_service/sales.php?m[0]=mim&m[1]=not&m[2]=del"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligence-subscriptions"
    When I go to "/sales_service/sales.php?m[0]=mim&m[1]=not&m[2]=delConf"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligence-subscriptions"
    When I go to "/sales_service/sales.php?m[0]=mim&m[1]=not&m[2]=add"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligence-subscriptions/add"

  Scenario: The MIM view pages should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=mim&m[1]=view&id=5261562"
    Then the response status code should be 404
    When I go to "/sales_service/sales.php?m[0]=mim&m[1]=view&id=10002"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligences/2/show"
    When I go to "/sales_service/sales.php?m[0]=mim&m[1]=view&m[2]=links&id=10002"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligences/2/show"
    When I go to "/sales_service/sales.php?m[0]=mim&m[1]=view&m[2]=files&id=10002"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligences/2/files"
    When I go to "/sales_service/sales.php?m[0]=mim&m[1]=view&m[2]=log&id=10002"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligences/2/show"
    When I go to "/sales_service/sales.php?m[0]=mim&m[1]=view&m[2]=tasks&id=10002"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligences/2/show"
    When I go to "/sales_service/sales.php?m[0]=mim&m[1]=view&m[2]=AddComment&id=10002"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligences/2/show"
    When I go to "/sales_service/sales.php?m[0]=mim&m[1]=view&m[2]=edit&id=10002"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/market-intelligences/2/edit"


