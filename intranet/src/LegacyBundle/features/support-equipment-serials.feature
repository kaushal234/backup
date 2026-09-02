Feature: The equipment serial numbers legacy pages should be redirected

#  @javascript
#  Scenario: Equipment serial numbers URL in legacy is redirected to the Symfony version
#    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
#    When I go to "/product_support/index.ps.php?m[0]=equipment&m[1]=view&m[2]=serials&id=37455"
#    And I should be on the exact url "/support/serials/1/show"
#    When I go to "/product_support/index.ps.php?m[0]=equipment&m[1]=view&m[2]=serials&m[3]=pre_add&id=37455"
#    And I should be on the exact url "/support/serials/1/edit"
#    When I go to "/product_support/index.ps.php?m[0]=equipment&m[1]=view&m[2]=serials&m[3]=add&id=37455"
#    And I should be on the exact url "/support/serials/1/edit"
#    When I go to "/product_support/index.ps.php?m[0]=equipment&m[1]=view&m[2]=serials&m[3]=edit&id=37455"
#    And I should be on the exact url "/support/serials/1/edit"
#    When I go to "/product_support/index.ps.php?m[0]=equipment&m[1]=view&m[2]=serials&m[3]=delete&id=37455"
#    And I should be on the exact url "/support/serials/1/edit"
#    Then I follow "ER (#37455)"
#    And I should be on the exact url "/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=37455"

