Feature: Parts SPQ

  Scenario: As an anonymous user, test that i'm not allowed to see SPQ pages
    When I go to "/parts/spq"
    Then I should be on "/login"
    When I go to "/parts/spq/quotations"
    Then I should be on "/login"
    When I go to "/parts/spq/quotations/add"
    Then I should be on "/login"
    When I go to "parts/spq/kpi"
    Then I should be on "/login"
    When I go to "/parts/spq/quotations/1/show"
    Then I should be on "/login"
    When I go to "/parts/spq/quotations/1/edit-header"
    Then I should be on "/login"
    When I go to "/parts/spq/quotations/1/edit-lines"
    Then I should be on "/login"
    When I go to "/parts/spq/quotations/1/files"
    Then I should be on "/login"
    When I go to "/parts/spq/quotations/1/logs"
    Then I should be on "/login"
    When I go to "/parts/spq/quotations/1/tasks"
    Then I should be on "/login"

  Scenario: As a basic user, test that i'm allowed to see SPQ dashboard
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "parts/spq"
    Then the response status code should be 200
    And The module should be "SPQ"
    And I should see "SPQ Quick Access"
    And I should see "My Quotations By SPH By Status"
    And I should see "Quotations By SPH By Status"
    And I should see "Last 10 Quotations"
    And I should see 2 "table.matrix-table" elements

  Scenario: Test quotations list
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "parts/spq/quotations"
    Then the response status code should be 200
    And I should see "SPQ Quick Access"
    And I should see "Filter"
    And I should see "Spare Parts Quotations"

  Scenario: Test that submitting a valid ID redirects to the given quotation
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "parts/spq/quotations"
    And I fill in the following:
      | app_id_search[id] | 12 |
    And press "quick-access-submit"
    Then I should be on "parts/spq/quotations/12/show"
    And the response status code should be 200

  Scenario: Test that submitting a valid quotation's ID redirects to the details of the quotation
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "parts/spq/quotations/1/show"
    Then the response status code should be 200
    And I should see "General"
    And I should see "Customer"
    And I should see "People Associated"
    And I should see "Quotation Lines"
    And I should see "Terms"
    And I should see "Invoice Address"
    And I should see "Delivery Address"

  Scenario: Test that submitting a valid quotation's ID redirects to the associated files
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "parts/spq/quotations/1/files"
    And I should see "Quotation Files"
    And I should see "Attached Files"

  Scenario: Test that submitting a valid quotation's ID redirects to the associated logs
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "parts/spq/quotations/1/logs"
    Then the response status code should be 200
    And I should be on "parts/spq/quotations/1/logs"
    And I should see "add a comment"

  Scenario: Test that submitting a valid ID redirects to the associated data
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "parts/spq/quotations/1/show"
    And I should see an "a[href$='/parts/spq/quotations/1/files']" element
    When I go to "/parts/spq/quotations/1/files"
    Then I should be on "parts/spq/quotations/1/files"
    And the response status code should be 200
    When I go to "parts/spq/quotations/1/show"
    And I should see an "a[href$='/parts/spq/quotations/1/logs']" element
    When I go to "/parts/spq/quotations/1/logs"
    Then I should be on "parts/spq/quotations/1/logs"
    And the response status code should be 200
    When I go to "parts/spq/quotations/1/logs"
    And I should see an "a[href$='/parts/spq/quotations/1/show']" element
    When I go to "/parts/spq/quotations/1/show"
    Then I should be on "parts/spq/quotations/1/show"
    And the response status code should be 200
    And I should see an "a[href$='/parts/spq/quotations/1/tasks']" element
    When I go to "/parts/spq/quotations/1/tasks"
    Then I should be on "parts/spq/quotations/1/tasks"
    And the response status code should be 200