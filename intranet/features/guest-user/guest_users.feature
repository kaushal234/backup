Feature: Guest Users

  Scenario: As an anonymous user, test that i'm not allowed to see guest users pages
    When I go to "/mis/guest-users"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/mis/guest-users/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see guest users pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/guest-users"
    Then the response status code should be 200
    When I go to "/mis/guest-users/320/show"
    Then the response status code should be 200
    And The module should be "GUEST USER"
    And I should see an "a[href$='/mis/guest-users']" element
    And I should see an "a[href$='/mis/guest-users/add']" element

  Scenario: As a basic user, test that i can add a guest user
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/guest-users/add"
    And I fill in the following:
      | guest_user[lastname]  | LEHUTT            |
      | guest_user[firstname] | JABBA             |
      | guest_user[username]  | jlebutt           |
      | guest_user[email]     | jabba@tld.fr      |
    And I select "business_unit_2" from "guest_user[businessUnit]"
    And I select "premise_1" from "guest_user[premise]"
    And I fill in "guest_user[enableAt]" with "07/15/2026"
    And I fill in "guest_user[plannedDisableAt]" with "07/15/2027"
    And I check "Yes"
    And I select "TPA Extended" from "guest_user[modules]"
    And I press "submit"
    Then I should see "Guest user successfully added."

  Scenario: As a basic user, test that i can edit a guest user
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/mis/guest-users/320/edit"
    When I fill in the following:
      | guest_user[lastname] | LEIA |
    And press "submit"
    And I should be on "/mis/guest-users/320/show"
    Then I should see "Guest user successfully edited."