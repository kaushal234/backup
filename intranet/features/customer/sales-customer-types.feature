Feature: Customers type

  Scenario: As an anonymous user, test that i'm not allowed to see customer type pages
    When I go to "/sales/customer-types"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to customer type  pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customer-types"
    Then the response status code should be 200
    And I should see "Customer types"
    And I should see "Top"
    And I should not see an "a[href$='/sales/customer-types/add']" element
    And I should not see an "a[href$='/sales/customer-types/5/edit']" element
    And I should not see an "a[href$='/sales/customer-types/5/delete']" element

  Scenario: As a superuser, test that i'm allowed to see customer type pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customer-types"
    Then the response status code should be 200
    And I should see an "a[href$='/sales/customer-types/add']" element
    And I should see an "a[href$='/sales/customer-types/5/edit']" element
    And I should see an "a[href$='/sales/customer-types/5/delete']" element

  Scenario: As a superuser, test that i can add a customer type
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customer-types/add"
    Then the response status code should be 200
    And I fill in "customer_type_form[name]" with "DabIsLife"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/sales/customer-types"
    And I should see "Customer type has been successfully saved"
    And I should see "DabIsLife"

  Scenario: As a superuser, test that i can edit a customer type
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customer-types/9/edit"
    Then the response status code should be 200
    And I fill in "customer_type_form[name]" with "SwagIsLife"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/sales/customer-types"
    And I should see "Customer type has been successfully saved"
    And I should see "SwagIsLife"

  Scenario: A superuser user can delete a customer type
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/customer-types/9/delete"
    Then the response status code should be 200
    And I should be on "/sales/customer-types"
    And I should see "Customer type has been successfully deleted"
