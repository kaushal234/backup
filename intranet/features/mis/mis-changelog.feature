Feature: Changelog

  Scenario: As an anonymous user, test that i'm not allowed to see modules pages
    Given I go to "/mis/change-logs"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, I can access change logs
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    And I go to "/mis/change-logs"
    Then the response status code should be 200
    And The module should be "MIS"
    And I should see "Mis Developments Releases"

  Scenario: Change logs can be filtered by MOO
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    And I go to "/mis/change-logs?filter_change_log[operationalOwner][value]=/people/10"
    Then the response status code should be 200
    Then I should see 4 "table.table tbody tr" elements

  Scenario: As a basic user, I can't access change logs
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    And I go to "/mis/change-logs/1/edit"
    Then I should not be on "/mis/change-logs/1/edit"
    And I should see "You do not have permissions"

  Scenario: As a superuser, I can access change logs
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    And I go to "/mis/change-logs/1/edit"
    And I should see "Edit Change Log"
    And I fill in "change_log_edit[message]" with "New description"
    And press "change_log_edit[submit]"
    Then the response status code should be 200
    Then I should be on "/mis/change-logs"
    And I should see "The change log has been successfully updated."
    And I should see "New description"
