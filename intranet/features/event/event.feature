Feature: Location Cleanliness

  Scenario: As an anonymous user, test that i'm not allowed to see events pages
    When I go to "/human-resources/events"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/human-resources/events"
    Then I should be on "/login"
    And I should see "Password"

  @authentication
  Scenario: As a superuser, I can see events page
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/events"
    And The module should be "Event"
    Then the response status code should be 200
    And I should see "Events"
    And I should not see an "a[href$='/human-resources/events/add']" element
    And I should not see an "a[href$='/human-resources/events/1/delete']" element

  @javascript
  Scenario: As a hr, I can add an event
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/events"
    And I should see an "a[href$='/human-resources/events/add']" element
    And I go to "/human-resources/events/add"
    And I should see "Add Event"
