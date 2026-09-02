Feature: Finance Families

  Scenario: As an anonymous user, test that i'm not allowed to see finance families pages
    When I go to "/finance/finance-families"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/finance/finance-families/3/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a superuser, test that i'm allowed to see finance families pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/finance-families"
    Then the response status code should be 200
    When I go to "/finance/finance-families/4/show"
    Then the response status code should be 200
    And The module should be "CAT"
    And I should see "Information"
    And I should see "Products Linked"
    And I should see an "a[href$='/finance/finance-families/4/edit']" element
    And I should see an "a[href$='/finance/finance-families/4/delete']" element

  Scenario: As a superuser, test that i can edit a finance family
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/finance-families/4/edit"
    Then the response status code should be 200
    When I fill in the following:
      | finance_form[name] | La FF Test  |
    And press "submit"
    Then the response status code should be 200
    And I should be on "/finance/finance-families/4/show"
    And I should see "La FF Test"

  Scenario: A superuser user can delete finance family
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/finance-families/28/show"
    Then the response status code should be 200
    And I should see an "a[href$='/finance/finance-families/28/delete']" element
    When I go to "/finance/finance-families/28/delete"
    Then the response status code should be 200
    And I should be on "/finance/finance-families"
    And I should see "Finance Family deleted successfully"
