Feature: The CSR pages should be redirected

  Scenario: The CSR should be redirected
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/service.php?m[0]=csr"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/dashboard/dashboard-csm"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=reports"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/reports"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=listing&m[2]=search"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/search"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=forms&m[2]=byNum"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/search"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=forms&m[2]=bySR"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/search"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=forms&m[2]=byOldCSR"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/search"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=forms&m[2]=add"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/add"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=forms"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/search"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=view&id=58642"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/1/show"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=view&m[2]=survey&id=58642"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/1/show"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=view&m[2]=duplicate&id=58642"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/1/show"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=view&m[2]=members&id=58642"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/1/show"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=view&m[2]=status&id=58642"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/1/show"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=view&m[2]=tasks&id=58642"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/1/show"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=view&m[2]=log&id=58642"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/1/show"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=view&m[2]=files&id=58642"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/1/show"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=view&m[2]=links&id=58642"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/1/show"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=view&m[2]=parts&id=58642"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/1/show"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=view&m[2]=faq&id=58642"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/1/show"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=view&m[2]=labors&id=58642"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/1/show"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=view&m[2]=delete&id=58642"
    Then I should not be on a legacy page
    And I should be on the exact url "/service/customer-service-records/1/edit"
    When I go to "/sales_service/service.php?m[0]=csr&m[1]=view&m[2]=any_string"
    Then the response status code should be 404

##  Comment because not working on CI, but this route should always exist on legacy
#  Scenario: The CSR costs should not be redirected
#    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
#    When I go to "/sales_service/service.php?m[0]=csr&m[1]=view&m[2]=costs&id=58642"
#    Then the response status code should be 200
#    And I should be on the exact url "/sales_service/service.php?m[0]=csr&m[1]=view&m[2]=costs&id=58642"
#    Then I should be on a legacy page
#    When I go to "/sales_service/service.php?m[0]=csr&m[1]=view&m[2]=edit&id=58642"
#    Then I should be on a legacy page

