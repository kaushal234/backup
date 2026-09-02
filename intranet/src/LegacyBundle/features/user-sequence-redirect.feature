Feature: The user sequence legacy pages should be redirected

  Scenario: The calendar should be redirected
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=hr.user.new"
    Then I should not be on a legacy page
    And I should be on the exact url "/directory/people/add"
