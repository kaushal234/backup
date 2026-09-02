Feature: The sales catalogue legacy pages should be redirected

  Scenario: The catalogue should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=catalogue"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/catalogue/types"

  Scenario: The catalogue should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/product_support/admin/models_admin.php"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/catalogue/products"

  Scenario: The detail pages should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=catalogue&m[1]=cat&id=27"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/catalogue/types/1"
    When I go to "/sales_service/sales.php?m[0]=catalogue&m[1]=cat&id=22517"
    Then the response status code should be 404

  Scenario: The detail pages should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=catalogue&m[1]=getFile&id=304"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/catalogue/families/1"
    When I go to "/sales_service/sales.php?m[0]=catalogue&m[1]=model&id=304"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/catalogue/families/1"
    When I go to "/sales_service/sales.php?m[0]=catalogue&m[1]=model&id=3054154"
    Then the response status code should be 404

  Scenario: The detail pages should be redirected
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales_service/sales.php?m[0]=catalogue&m[1]=model&[2]=files&id=304"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/catalogue/families/1"
    When I go to "/sales_service/sales.php?m[0]=catalogue&m[1]=model&[2]=history&id=304"
    Then I should not be on a legacy page
    And I should be on the exact url "/sales/catalogue/families/1"
