Feature: The events legacy pages should be redirected

  Scenario: The events should be redirected
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/calendar/calendar.php?m[0]=events"
    Then I should not be on a legacy page
    And I should be on the exact url "/human-resources/events"
    When I go to "/calendar/calendar.php?m[0]=events&m[1]=byYear"
    Then I should not be on a legacy page
    And I should be on the exact url "/human-resources/events"
    When I go to "/calendar/calendar.php?m[0]=events&m[1]=byMonth"
    Then I should not be on a legacy page
    And I should be on the exact url "/human-resources/events"
    When I go to "/calendar/events/holidays_admin.php"
    Then I should not be on a legacy page
    And I should be on the exact url "/human-resources/events"
