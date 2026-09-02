Feature: The vendor warranty claim legacy pages should be redirected

  Scenario: The vendor warranty claim homepage should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/manufacturing/pur/dev.php?m[0]=vwc"
    Then I should not be on a legacy page
    And I should be on the exact url "/purchasing/vendor-warranty-claims"
    When I go to "/manufacturing/pur/dev.php?m[0]=vwc&m[1]=view&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/purchasing/vendor-warranty-claims/1/show"
    When I go to "/manufacturing/pur/dev.php?m[0]=vwc&m[1]=view&id=605256"
    Then the response status code should be 404
    When I go to "/manufacturing/pur/vwc/vwc_admin.php"
    Then I should not be on a legacy page
    And I should be on the exact url "/purchasing/vendor-warranty-claims"
    When I go to "/manufacturing/pur/dev.php?m[0]=vwc&m[1]=view&m[2]=edit&id=1"
    Then I should not be on a legacy page
    And I should be on the exact url "/purchasing/vendor-warranty-claims/1/edit"
    When I go to "/manufacturing/pur/dev.php?m[0]=vwc&m[1]=reports"
    Then I should not be on a legacy page
    And I should be on the exact url "/purchasing/vendor-warranty-claims/report"
