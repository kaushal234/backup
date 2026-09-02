Feature: Sales / Orders

  Scenario: As an anonymous user, test that i'm not allowed to see SOR pages
    When I go to "/sales/orders"
    Then I should be on "/login"
    When I go to "/sales/orders/add"
    Then I should be on "/login"
    When I go to "/sales/orders/1/show"
    Then I should be on "/login"
    When I go to "/sales/orders/1/logs"
    Then I should be on "/login"
    When I go to "/sales/orders/1/tasks"
    Then I should be on "/login"
    When I go to "/sales/orders/1/files"
    Then I should be on "/login"
    When I go to "/sales/orders/1/edit"
    Then I should be on "/login"
    When I go to "/sales/orders/1/delete"
    Then I should be on "/login"
    When I go to "/sales/orders/2/duplicate"
    Then I should be on "/login"
    When I go to "/sales/orders/transfer"
    Then I should be on "/login"
    When I go to "/sales/orders/1/status/IN PROGRESS"
    Then I should be on "/login"

  Scenario: As a basic user, I'm redirected to the homepage
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/orders"
    Then I should not be on "/sales/orders"
    And I should see "You do not have permissions"

  Scenario: As a sales admin, I can access the module dashboard with the data table and the SSO/status report
    Given I authenticate as "user-sa@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/orders"
    Then I should be on "/sales/orders"
    And The module should be "SOR"
    And I should see "Filter"
    And I should see "SOR Count By SSO By Status"
    And I should see an "table.matrix-table" element
    And I should see an "a[href$='/sales/orders/add']" element

  Scenario: As a sales admin, I can access a SOR detail
    Given I authenticate as "user-sa@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/orders/1/show"
    Then I should be on "/sales/orders/1/show"
    And I should see 3 ".association-table" elements
    # ASM / buyer / end user / BAAN so / Add SOL
    And I should see an "a[href$='/directory/people/31/show']" element
    And I should see an "a[href$='/sales/customers/1/show']" element
    And I should see an "a[href$='/sales/customers/35/show']" element
    And I should see an "a[href$='/finance/finance.php?m%5B0%5D=so&m%5B1%5D=view&erp=540&id=123']" element
    And I should see an "a[href$='/sales_service/sales.php?m%5B0%5D=sol&m%5B1%5D=form&m%5B2%5D=addSOL&pid=18894']" element

  Scenario: Quick access should work with id and legacy Id
    Given I authenticate as "user-sa@tld.fr" with "P@ssw0rd15chars"
    When I go to "sales/orders"
    And I fill in the following:
      | app_id_search[id] | 1 |
    And press "id-quick-access-submit"
    Then I should be on "sales/orders/1/show"
    And the response status code should be 200
    Then I go to "sales/orders"
    And I fill in the following:
      | app_legacy_id_search[legacyId] | 18894 |
    And press "legacy-id-quick-access-submit"
    Then I should be on "sales/orders/1/show"
    And the response status code should be 200

  Scenario: Test that it is possible to navigate in the menu
    Given I authenticate as "user-sa@tld.fr" with "P@ssw0rd15chars"
    When I go to "sales/orders/1/show"
    Then I should be on "sales/orders/1/show"
    And the response status code should be 200
    And I should see an "a[href$='sales/orders/1/files']" element
    When I go to "sales/orders/1/files"
    Then I should be on "sales/orders/1/files"
    And the response status code should be 200
    And I should see an "a[href$='sales/orders/1/logs']" element
    When I go to "sales/orders/1/logs"
    Then I should be on "sales/orders/1/logs"
    And the response status code should be 200
    And I should see an "a[href$='sales/orders/1/tasks']" element
    When I go to "sales/orders/1/tasks"
    Then I should be on "sales/orders/1/tasks"
    And the response status code should be 200
    And I should see an "a[href$='sales/orders/1/edit']" element
    When I go to "sales/orders/1/edit"
    Then I should be on "sales/orders/1/edit"
    And the response status code should be 200
    And I should see "Delete"
    When I go to "sales/orders/1/delete"
    Then I should be on "sales/orders/1/delete"
    And the response status code should be 200
    And I should see an "a[href$='sales/orders/1/duplicate']" element
    When I go to "sales/orders/1/duplicate"
    Then I should be on "sales/orders/1/duplicate"
    And the response status code should be 200

  Scenario: Test that it is possible duplicate a SOR
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "sales/orders/1/duplicate"
    And I check "app_order_duplicate[confirm]"
    And press "duplicate"
    Then I should be on "/sales/orders/41/show"
    And I should see "Sales order has successfully been created"

  Scenario: Test that it is possible create a SOR without CUNO if the location doesn't have an EPR in BAAN
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "sales/orders/add"
    And I select "/locations/31" from "order[sso]"
    And I select "/people/31" from "order[asm]"
    And I select "/sales/customers/1" from "order[endUser]"
    And I select "0" from "order[newCustomer]"
    And I fill in "order[customerPurchaseOrders][0]" with "789"
    And press "Submit"
    Then I should be on "/sales/orders/add"
    # Usually done via Javascript
    And I select "/juridical_locations/3" from "order[juridicalLocation]"
    And press "Submit"
    Then I should be on "/sales/orders/42/show"
    And I should see "Sales order has successfully been created"

  Scenario: Test that it is possible create a SOR without CUNO with customer **DEMO**
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "sales/orders/add"
    And I select "/locations/28" from "order[sso]"
    And I select "/people/31" from "order[asm]"
    And I select "/sales/customers/43" from "order[endUser]"
    And I select "0" from "order[newCustomer]"
    And I fill in "order[customerPurchaseOrders][0]" with "789"
    And press "Submit"
    Then I should be on "/sales/orders/add"
    # Usually done via Javascript
    And I select "/juridical_locations/1" from "order[juridicalLocation]"
    And press "Submit"
    Then I should be on "/sales/orders/43/show"
    And I should see "Sales order has successfully been created"

  Scenario: Test that it is possible edit a SOR
    Given I authenticate as "user-asm@tld.fr" with "P@ssw0rd15chars"
    When I go to "sales/orders/1/edit"
    And I select "/sales/customers/1" from "order[endUser]"
    And I select "/sales/customers/1" from "order[buyer]"
    And I select "AA1057" from "order[inforLnBusinessPartnerCode]"
    And press "Submit"
    Then I should be on "/sales/orders/1/show"
    And I should see "Sales order has successfully been edited"
    And I should see "AA1057"

  Scenario: Test that it is possible delete a SOR
    Given I authenticate as "user-sa@tld.fr" with "P@ssw0rd15chars"
    When I go to "sales/orders/2/delete"
    And I check "app_order_delete[confirm]"
    And press "delete"
    Then I should be on "/sales/orders"
    And I should see "Sales order has successfully been deleted"
