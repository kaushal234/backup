Feature: Parts / transportation_note

  Scenario: As an anonymous user, test that i'm not allowed to see transportation note pages
    When I go to "/parts/transportation-note"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/parts/transportation-note/add"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/parts/transportation-note/1/show"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/parts/transportation-note/1/edit"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/parts/transportation-note/1/logs"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see some transportation note pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/transportation-note"
    Then the response status code should be 200
    And I should be on "/parts/transportation-note"
    And I should see an "table.report-table" element
    And I should not see an "a[href$='/parts/transportation-note/add']" element
    And I should see "Transportation Note"
    When I go to "/parts/transportation-note/1/show"
    Then the response status code should be 200
    And I should be on "/parts/transportation-note/1/show"
    And I should see "Permitted to vomit in the truck !"
    And I should not see an "a[href$='/parts/transportation-note/add']" element
    And I should not see an "a[href$='/parts/transportation-note/1/edit']" element
    And I should see an "a[href$='/parts/transportation-note/1/logs']" element
    When I go to "/parts/transportation-note/1/logs"
    Then the response status code should be 200
    And I should be on "/parts/transportation-note/1/logs"
    And I should not see an "a[href$='/parts/transportation-note/add']" element
    And I should not see an "a[href$='/parts/transportation-note/1/edit']" element
    And I should see an "a[href$='/parts/transportation-note/1/logs']" element
    When I go to "/parts/transportation-note"
    When I go to "/parts/transportation-note/add"
    Then the response status code should be 200
    And I should be on "/parts/transportation-note"
    And I should see "You do not have permissions"
    When I go to "/parts/transportation-note/2/edit"
    Then the response status code should be 200
    And I should be on "/parts/transportation-note"
    And I should see "You do not have permissions"

  Scenario: Test that it is possible to navigate in the menu
    Given I authenticate as "user-spm@tld.fr" with "P@ssw0rd15chars"
    When I go to "parts/transportation-note"
    And I should see an "a[href$='/parts/transportation-note']" element
    When I go to "/parts/transportation-note"
    Then I should be on "parts/transportation-note"
    And the response status code should be 200
    And I should see an "a[href$='parts/transportation-note/add']" element
    When I go to "parts/transportation-note/add"
    Then I should be on "parts/transportation-note/add"
    And the response status code should be 200
    When I go to "parts/transportation-note/1/show"
    And I should see an "a[href$='/parts/transportation-note/1/logs']" element
    When I go to "/parts/transportation-note/1/logs"
    Then I should be on "parts/transportation-note/1/logs"
    And the response status code should be 200
    And I should see an "a[href$='parts/transportation-note/1/edit']" element
    When I go to "/parts/transportation-note/1/edit"
    Then I should be on "parts/transportation-note/1/edit"
    And the response status code should be 200
    And I should see an "a[href$='/parts/transportation-note/1/show']" element
    When I go to "/parts/transportation-note/1/show"
    Then I should be on "parts/transportation-note/1/show"
    And the response status code should be 200
    And I should see an "a[href$='parts/transportation-note/add']" element
    When I go to "/parts/transportation-note/add"
    Then I should be on "parts/transportation-note/add"
    And the response status code should be 200

  Scenario: Test that it is possible add a note
    Given I authenticate as "user-spm@tld.fr" with "P@ssw0rd15chars"
    When I go to "parts/transportation-note/add"
    When I select "/countries/4" from "transportation_note[country]"
    When I fill in "transportation_note[note]" with "Yup"
    And press "Submit"
    Then I should be on "parts/transportation-note/4/show"
    And I should see "Yup"

  Scenario: Test that it is possible edit a note
    Given I authenticate as "user-spm@tld.fr" with "P@ssw0rd15chars"
    When I go to "parts/transportation-note/2/edit"
    When I fill in "note" with "Rerum earum unde"
    And press "Submit"
    Then I should be on "parts/transportation-note/2/show"
    And I should see "Rerum earum unde"
