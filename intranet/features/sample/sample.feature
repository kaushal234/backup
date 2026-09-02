Feature: Directory / Sample Form

  @javascript
  Scenario: As superuser, test that I can submit sample form
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/sample-form"
    And I should be on the exact url "/sample-form"
    And I wait until I see "User Name"
    And I fill in "username" with "my username"
    And I fill in "description" with "my description"
    Then I fill in dropdown "departure1" with "CDG"
    Then I fill in dropdown "arrival1" with "CDG"
    Then I fill in dropdown "departure2" with "CDG"
    Then I fill in dropdown "arrival2" with "CDG"
    Then I fill in dropdown "airport" with "CDG"
    Then I fill in dropdown "airports" with "CDG"
    Then I fill in rich textarea "travelDetails" with "My Travel Details"
    Then I fill in date picker "departureDate" with "today"
    Then I check "terms"
    Then I check "conditions"
    Then I check radio "passportHolder" with "yes"
    And press "Submit"
    And I wait until I see "Saved"
