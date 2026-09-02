@contact-campaign

Feature: Contact Campaign

  Scenario: As an anonymous user, test that i'm not allowed to see contact campaign pages
    When I go to "/contact-campaigns"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/contact-campaigns/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, I should allowed to go on contact campaign
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/contact-campaigns"
    Then the response status code should be 200
    And I should be on "/contact-campaigns/"
    And I should not see an "a[href$='/contact-campaigns/add']" element
    And I should see "Contact Campaigns"
    And I should see "Items per page"
    When I go to "/contact-campaigns/1/show"
    Then the response status code should be 200
    And I should be on "/contact-campaigns/1/show"
    And I should not see an "a[href$='/contact-campaigns/add']" element
    And I should not see an "a[href$='/contact-campaigns/1/edit']" element
    And I should see "Christmas day"
    And I should see "User Coo"
    And I should see "DRAFT"
    And I should see "SAY_MY_NAME"
    And I should see "Contacts"
    And I should see "Items per page"

  Scenario: As a ceo user, I should allowed to go on contact campaign
    Given I authenticate as "user-ceo@tld.fr" with "P@ssw0rd15chars"
    When I go to "/contact-campaigns"
    Then the response status code should be 200
    And I should be on "/contact-campaigns/"
    And I should see an "a[href$='/contact-campaigns/add']" element
    And I should see "Contact Campaigns"
    And I should see "Items per page"
    When I go to "/contact-campaigns/1/show"
    Then the response status code should be 200
    And I should be on "/contact-campaigns/1/show"
    And I should see an "a[href$='/contact-campaigns/add']" element
    And I should see an "a[href$='/contact-campaigns/1/edit']" element
    And I should see "Christmas day"
    And I should see "User Coo"
    And I should see "DRAFT"
    And I should see "SAY_MY_NAME"
    And I should see "Contacts"
    And I should see "Items per page"