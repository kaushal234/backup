Feature: Some Technician On Call legacy pages should be redirected

  Scenario: Some Technician On Call pages should be redirected
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/service.php?m[0]=toc"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/dashboard/dashboard-csm"
    When I go to "/sales_service/service.php?m[0]=toc&m[1]=listing"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/technician-on-calls/search"
    When I go to "/sales_service/service.php?m[0]=toc&m[1]=reports"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/technician-on-calls/reports"
    When I go to "/sales_service/service.php?m[0]=toc&m[1]=kpi"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/technician-on-calls/kpi"
    When I go to "/sales_service/service.php?m[0]=toc&m[1]=form"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/technician-on-calls/search"
    When I go to "/sales_service/service.php?m[0]=toc&m[1]=form&m[2]=new"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/technician-on-calls/add"
    When I go to "/sales_service/service.php?m[0]=toc&m[1]=form&m[2]=new4"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/technician-on-calls/add"
    When I go to "/sales_service/service.php?m[0]=toc&m[1]=form&m[2]=other"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/technician-on-calls/search"
    When I go to "/sales_service/service.php?m[0]=toc&m[1]=view"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/dashboard/dashboard-csm"
    When I go to "/sales_service/service.php?m[0]=toc&m[1]=view&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/technician-on-calls/1/show"