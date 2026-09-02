Feature: Gitlab

  Scenario: As an anonymous user, test that i'm not allowed to see modules pages
    Given I go to "/mis/gitlab/merge_requests"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, I can't access the merge requests report
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    And I go to "/mis/gitlab/merge_requests"
    Then I should not be on "/mis/gitlab/merge_requests"
    And I should see "You do not have permissions"

  Scenario: As a MISM user, I can access the merge requests report
    Given I authenticate as "user-mism@tld.fr" with "P@ssw0rd15chars"
    And I go to "/mis/gitlab/merge_requests"
    Then the response status code should be 200
    And The module should be "MIS"
    And I should see "Merged merge requests"

  Scenario: Merge requests can be filtered by merged_at date
    Given I authenticate as "user-mism@tld.fr" with "P@ssw0rd15chars"
    And I go to "/mis/gitlab/merge_requests"
    And I fill in "merge_request_filters[merged_after]" with "01/01/2026"
    And I fill in "merge_request_filters[merged_before]" with "02/01/2026"
    And I press "FILTER"
    Then the response status code should be 200
