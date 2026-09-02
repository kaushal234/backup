Feature: SMW Meeting

  Scenario: As an anonymous user, test that i'm not allowed to see smw meeting pages
    When I go to "/quality/smw-meeting"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/manufacturing/smw-meeting"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see smw meeting pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/smw-meeting"
    Then the response status code should be 200
    And The module should be "SMW"
    And I should see "Quality / SMW"
    And I should see "Location"
    And I should see "Title"
    And I should see "Description of the link"
    And I should see "Link"
    When I go to "/manufacturing/smw-meeting"
    Then the response status code should be 200
    And I should see "Manufacturing / SMW"
    And I should see "Location"
    And I should see "Title"
    And I should see "Description of the link"
    And I should see "Link"
    When I go to "/engineering/smw-meeting"
    Then the response status code should be 200
    And I should see "Engineering / SMW"
    And I should see "Location"
    And I should see "Title"
    And I should see "Description of the link"
    And I should see "Link"
    When I go to "/purchasing/smw-meeting"
    Then the response status code should be 200
    And I should see "Purchasing / SMW"
    And I should see "Location"
    And I should see "Title"
    And I should see "Description of the link"
    And I should see "Link"
    When I go to "/service/smw-meeting"
    Then the response status code should be 200
    And I should see "Service / SMW"
    And I should see "Location"
    And I should see "Title"
    And I should see "Description of the link"
    And I should see "Link"

  Scenario: As a basic user, test that i'm allowed to see ODP stability report
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/on_time_delivery_planning/reports/stability?manufacturerLocation%5B0%5D=/locations/11"
    Then the response status code should be 200
    And I should see "ODP Stability"
    And I should see "Actual GTs done last month/Late GT/ Estimated GT in future 6 months"
    And I should see "Late equipment record"

  Scenario: As a basic user, test that i'm allowed to see smw charts
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/smw-meeting/charts"
    Then the response status code should be 200
    And I should see "1.2 - Commodities & Transport trends"
    And I should see "Steel FOB China"
    And I should see "Copper"
    And I should see "Aluminium"
    And I should see "Lithium"
    And I should see "SCFI"