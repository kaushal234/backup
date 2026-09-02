Feature: Directory / Mentoring

  Scenario: As an anonymous user, test that i'm not allowed to see mentoring page
    When I go to "/directory/mentoring"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a superuser user, test that i'm allowed to see mentoring page
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/mentoring"
    Then the response status code should be 200
    And The module should be "USER"
    And I should see "Mentoring Listing"
    And I should see "Mentee"
    And I should see "Mentor"

  Scenario: As a hr user, test that i'm allowed to see download link
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/mentoring"
    Then the response status code should be 200
    And I should see an "a[href$='/directory/mentoring/download']" element

  Scenario: As a hr user, test that i can download mentoring list
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/mentoring/download"
    Then the response status code should be 200
    Then I should see response headers "content-type" with "text/csv; charset=utf-8"
    Then I should see response headers "content-disposition" with 'inline; filename=mentors.csv'

  Scenario: As a basic user, test that i can not download mentoring list
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/mentoring/download"
    Then I should see "You do not have permissions"

