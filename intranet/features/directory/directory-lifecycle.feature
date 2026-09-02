Feature: Directory / People

  Scenario: As an anonymous user, test that i'm not allowed to see new comers pages
    When I go to "/human-resources/new-comers/report"
    Then I should be on "/login"
    And I should see "Password"

  @javascript
  Scenario: As a basic user, test that i'm allowed to see people pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/new-comers/report"
    Then I wait until I see "Recent and Coming New Comers"
    Then I should see "Page content is filtered based on your access rights."

  @javascript
  Scenario: As a superuser user, test that i'm allowed to see people pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/new-comers/report"
    Then I wait until I see "Recent and Coming New Comers"
    When I click on the 1st ".btn[data-bs-target='#updateTasksModal-111']" element
    Then I wait until I see "Confirmed"
    Then I wait until I see "Denied"
    And I wait for "a[data-url$='/human-resources/directory/third_party_apps/7/grant_access_accepted_ajax/38']" element
    When I click on the "a.js-access-action[data-url$='/human-resources/directory/third_party_apps/7/grant_access_accepted_ajax/38']" element and accept confirm
    Then I wait 5 seconds
    And I wait for "#updateTasksModal-111 table tr:first-child span.btn.btn-default.btn-xs:contains('Confirmed')" element

  Scenario: As an anonymous user, test that i'm not allowed to see recent_and_coming_leavers pages
    When I go to "/human-resources/leavers/report"
    Then I should be on "/login"
    And I should see "Password"

  @javascript
  Scenario: As a basic user, test that i m allowad to see already leavers but i'm not allowed to see coming leavers pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/leavers/report"
    Then I wait until I see "Recent (last 3 months) And Coming Leavers"
    Then I should see "Page content is filtered based on your access rights."

  @javascript
  Scenario: As a hr user, test that i'm allowed to see recent and coming leavers pages
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/leavers/report"
    Then I wait until I see "Recent (last 3 months) And Coming Leavers"
    Then I wait until I see "PARTIE-DEPUIS-PEU jean"
    Then I wait until I see "PARTIE bientot"

  @javascript
  Scenario: As a superuser user, test that i'm allowed to see recent and coming leavers pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/leavers/report"
    Then I wait until I see "Recent (last 3 months) And Coming Leavers"
    When I click on the 1st ".btn[data-bs-target='#updateTasksModal-107']" element
    Then I wait until I see "Confirmed"
    Then I wait until I see "Denied"
    And I wait for "a[data-url$='/human-resources/directory/third_party_apps/8/remove_access_accepted_ajax/38']" element
    When I click on the "a.js-access-action[data-url$='/human-resources/directory/third_party_apps/8/remove_access_accepted_ajax/38']" element and accept confirm
    Then I wait 5 seconds
    And I wait for "span.btn.btn-default.btn-xs:contains('Confirmed')" element
