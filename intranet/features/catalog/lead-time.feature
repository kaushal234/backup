Feature: Standard Lead Time

  Scenario: As an anonymous user, test that i'm not allowed to see standard lead time pages
    When I go to "/sales/catalogue/lead_times"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see lead time pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/lead_times"
    Then the response status code should be 200
    And The module should be "SLT"
    And I should not see an "a[href$='/sales/catalogue/lead_times/29/edit']" element

  Scenario: As a basic user, test that i'm not allowed to created a lead time
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/lead_times/11/edit"
    Then I should not be on "/sales/catalogue/lead_times/11/edit"
    And I should see "You do not have permissions"

  Scenario: As a basic psm, test that i'm allowed to see lead times of my factory
    Given I authenticate as "user-psm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/lead_times?my-locations=1"
    Then I should be on "/sales/catalogue/lead_times?my-locations=1"
    And I should see "Standard lead time: location_parts, location_factory, location_warehouse"

  Scenario: As a psm user, test that i'm allowed to see the lead time creation page
    Given I authenticate as "user-psm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/lead_times/29/edit"
    Then I should be on "/sales/catalogue/lead_times/29/edit"
    Then the response status code should be 200
    And The module should be "SLT"

  Scenario: : As a psm user, test that i'm allowed to created or edit a lead time
    Given I authenticate as "user-psm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/lead_times/29/edit"
    Then the response status code should be 200
    Then I fill in "lead_time[leadTimes][20][weeks]" with "1"
    Then I fill in "lead_time[leadTimes][20][description]" with "The unit will be delivery soon"
    Then I uncheck "lead_time_fullUpdate"
    And I press "lead_time[submit]"
    Then the response status code should be 200
    Then I should be on "/sales/catalogue/lead_times"

  Scenario: : As a psm user, test that i'm allowed to delete a lead time
    Given I authenticate as "user-psm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sales/catalogue/lead_times/29/edit"
    Then the response status code should be 200
    Then I check "lead_time_leadTimes_20_reset"
    And I press "lead_time[submit]"
    Then the response status code should be 200
    Then I should be on "/sales/catalogue/lead_times"
