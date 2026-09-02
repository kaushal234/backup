Feature: Authentication

  @authentication
  Scenario: As an anonymous user, test that I must authenticate myself
    Given I go to "/logout"
    Then I go to the homepage
    Then the response status code should be 200
    And I should be on "/login"
    And I should see "Password"

  @authentication
  Scenario: As an expired user, I authenticate myself successfully with an expired account and access homepage
    Given I authenticate as "user-expired@tld.fr" with "P@ssw0rd15chars"
    Then the response status code should be 200
    And I should be on "/"
    And I should see "Internal News"
    When I go to "/acronyms"
    Then the response status code should be 200
    And I should be on "/account/change-password"

  @authentication
  Scenario: As an anonymous user, I authenticate myself successfully
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    Then the response status code should be 200
    And I should be on "/"
    And I should see "Internal News"

  @authentication
  Scenario: As a superuser, test that i can impersonate a user
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/account?_switch_to=/people/11&_username=user-basic@tld.fr"
    Then I should see "user-basic@tld.fr"
    When I go to "/account?_switch_exit"
    And I should be on "/"

  @authentication
  Scenario: As a basic user, test that i can't impersonate a user
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/account?_switch_to=/people/2&_username=user-rep"
    Then I should see "You do not have permissions"

  @authentication
  Scenario: As a user with no full intranet access, i can authenticate with my alternate email but I can't see all the pages
    Given I authenticate as "flore.shop" with "P@ssw0rd15chars"
    When I go to the homepage
    Then I should be on "/"
    And the response status code should be 200
    When I go to "/directory/locations"
    Then I should see "You do not have permissions"

  @authentication
  Scenario: As an anonymous user, test that I can access the forgot password page
    Given I go to "/forgot-password"
    Then the response status code should be 200
    And I should be on "/forgot-password"
    And I should see "Email"
    And I should see "Forgot your password"

  @authentication
  Scenario: As an anonymous user, test that I can access the SSO page
    Given I go to "/sso-login"
    Then the response status code should be 200
    And I should be on "/sso-login"
    And I should see "Connect with Microsoft"
    And I should see "Continue"
