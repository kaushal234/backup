Feature: Finance Forex

  Scenario: As an anonymous user, test that i'm not allowed to see finance families pages
    When I go to "/finance/forex"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/finance/forex/search"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/finance/forex/add"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/finance/forex/1/edit"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/finance/currencies/add"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see forex homepage
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/forex"
    Then the response status code should be 200
    And The module should be "FRX"
    And I should see an "a[href$='/finance/forex/']" element
    And I should see 6 "table.matrix-table" elements

  Scenario: As a basic user, test that i'm not allowed to access other forex pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/forex/search"
    Then the response status code should be 200
    Then I should not be on "/finance/forex/search"
    And I should see "You do not have permissions"
    When I go to "/finance/forex/add"
    Then the response status code should be 200
    Then I should not be on "/finance/forex/add"
    And I should see "You do not have permissions"
    When I go to "/finance/forex/1/edit"
    Then the response status code should be 200
    Then I should not be on "/finance/forex/1/edit"
    And I should see "You do not have permissions"

  Scenario: As a basic user, test that i can't add or edit an exchange rate
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/forex/1/edit"
    Then I should not be on "/finance/forex/1/edit"
    And I should see "You do not have permissions"
    When I go to "/finance/forex/add"
    Then I should not be on "/finance/forex/add"
    And I should see "You do not have permissions"

  Scenario: As a superuser, test that i'm allowed to see forex homepage
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/forex"
    Then the response status code should be 200
    And I should see an "a[href$='/finance/forex/']" element
    And I should see an "a[href$='/finance/forex/search']" element
    And I should see an "a[href$='/finance/forex/add']" element
    And I should see an "a[href$='/finance/currencies/add']" element
    And I should see 6 "table.matrix-table" elements

  Scenario: As a superuser, test that i can download exchange rates as CSV file
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/forex/search"
    When I press "download"
    Then the response status code should be 200
    Then I should see response headers "content-type" with "text/csv; charset=utf-8"
    Then I should see response headers "content-disposition" with 'inline; filename=exchange_rates.csv'

  Scenario: As a superuser, test that i can add an exchange rate
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/forex/add"
    Then the response status code should be 200
    When I select "AVG" from "type"
    And I select "/finance/currencies/7" from "currency"
    And I fill in the following:
      | applicatedOn | 01/2019  |
      | rate         | 2.3         |
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/finance/forex/search"
    And I should see "Exchange rate has successfully been created"

  Scenario: As a basic, test that i can't add a currency
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/currencies/add"
    Then I should not be on "/finance/currencies/add"
    And I should see "You do not have permissions"

  Scenario: As a superuser, test that i can add a currency
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/currencies/add"
    Then the response status code should be 200
    When I fill in "currency_form[name]" with "LSD"
    And press "Submit"
    Then the response status code should be 200
    And I should be on "/finance/forex/"
    And I should see "Currency has been successfully created"
