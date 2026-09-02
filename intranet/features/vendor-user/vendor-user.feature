Feature: Vendor users
  Scenario: As an anonymous user, test that i'm not allowed to see vendor users pages
    When I go to "/purchasing/vendor-users"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a Basic user, test that i'm allowed to see vendor users page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-users"
    Then the response status code should be 200
    And I should see "Firstname"
    And I should see "Lastname"
    And I should see "email"
    And I should see "LN Contact"
    And I should see "Created At"
    And I should see "Last login"

  Scenario: As a Basic users, test that i can not not see vendor user page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-users/300"
    Then I should not be on "/purchasing/vendor-users/300"
    And I should see "You do not have permissions"

  Scenario: As a buyer, test i see an show vendor user link
    Given I authenticate as "user-buyer@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-users/302"
    And I should see an "a[href$='purchasing/vendor-users/302']" element

  Scenario: As a buyer, test that i'm allowed to see vendor user page
    Given I authenticate as "user-buyer@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-users/300"
    Then the response status code should be 200
    And I should see "Edit"
    And I should see "Shadow"
    And I should see "VENDOR USER #300"
    And I should see "Details"
    And I should see "Logs"

  Scenario: As a Basic users, test that i can not edit a vendor users
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-users/300/edit"
    Then I should not be on "/purchasing/vendor-users/300/edit"
    And I should see "You do not have permissions"

  Scenario: As a buyer, test that i can edit a vendor users
    Given I authenticate as "user-buyer@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-users/300/edit"
    Then the response status code should be 200
    And I fill in "vendor_user[erpIdentifier]" with "123456789"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/purchasing/vendor-users"
    And I should see "123456789"

  Scenario: As a buyer, test i see an impersonate link
    Given I authenticate as "user-buyer@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-users/302"
    And I should see an "a[href$='?_impersonate=/purchasing/vendor_users/302&_userIdentifier=devteam@tld-america.com']" element


