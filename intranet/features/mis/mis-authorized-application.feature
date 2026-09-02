Feature: Mis / Authorized Application

  Scenario: As an anonymous user, test that I'm not allowed to see authorized applications pages
    When I go to "/mis/authorized-applications"
    Then I should be on "/login"
    And I should see "Password"

  @javascript
  Scenario: As a basic user, test that I'm not allowed to see authorized applications
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/authorized-applications"
    Then I should be on "/account"

  Scenario: As a superuser, test that i'm allowed to see authorized applications
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/authorized-applications"
    Then the response status code should be 200
    And The module should be "MIS"
    And I should see "Authorized Applications"
    And I should see an "a[href^='/mis/authorized-applications/1/switch']" element
    And I should see an "a[href^='/mis/authorized-applications/1/delete']" element
    And I should not see a "pre[id=authorized-application-key]" element

  Scenario: As a superuser, test that i can add an authorized application
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/authorized-applications"
    Then the response status code should be 200
    When I fill in "authorized_application[name]" with "LN et le divin enfant"
    When I fill in "authorized_application[keyExpiresOn]" with "10/04/2099"
    And press "submit"
    Then I should be on the exact url "/mis/authorized-applications"
    And I should see a "pre[id=authorized-application-key]" element

