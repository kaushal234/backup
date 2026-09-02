Feature: Account receivable

  Scenario: As an anonymous user, test that i'm not allowed to see account receivable pages
    When I go to "/finance/account-receivables/dashboard"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/finance/account-receivables/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As an anonymous user, test that i'm not allowed to see invoice records pages
    When I go to "/finance/invoice-records"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/finance/invoice-records/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a superuser, test that i'm allowed to see account receivable pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/account-receivables/dashboard"
    Then the response status code should be 200
    When I go to "/finance/account-receivables/1/show"
    Then the response status code should be 200
    And The module should be "AR"
    And I should see "Account Receivable Details"
    When I go to "/finance/account-receivables/my-tasks"
    Then the response status code should be 200
    And The module should be "AR"
    And I should see "Review of all Past Due > 60 days AND > 20K€ or only > 100K€"
    When I go to "/finance/account-receivables/search"
    Then the response status code should be 200
    And The module should be "AR"
    And I should see "Last 10 Account Receivables"

  Scenario: As a superuser, test that i'm allowed to see invoice records pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/invoice-records"
    Then the response status code should be 200
    And I should see "Last 10 Invoice Records"
    When I go to "/finance/invoice-records/1/show"
    Then the response status code should be 200
    And The module should be "AR"
    And I should see "Invoice Record Details"
    And I should see an "a[href$='/finance/invoice-records/1/edit']" element

  Scenario: As a superuser, test that i can add invoice record revised due date
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/account-receivables/2/show"
    And I fill in "formRevisedDueDate[revisedDueDate]" with "06/01/2028"
    And press "formRevisedDueDate[submit]"
    Then the response status code should be 200
    And I should be on "/finance/account-receivables/2/show"
    And I should see "Invoice Record Details"

  Scenario: As a superuser, test that i can edit invoice record
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/finance/invoice-records/1/edit"
    And I fill in "form[expectedPaymentDate]" with "06/01/2028"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/finance/invoice-records/1/show"
