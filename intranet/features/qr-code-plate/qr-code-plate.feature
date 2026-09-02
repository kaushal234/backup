@qr_code_plate
Feature: Directory / Sample Form

  @javascript
  Scenario: As superuser, test that I can generate name plate
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/support/equipment_records/27/name-plate"
    And I wait until I see "Name Plate Generator"
    And I wait until I see "LOCATION_FACTORY, ALBUQUERQUE, US"
    And I wait until I see "NBL-E"
    Then I fill in date picker "mfgDate" with "01/2025"
    And I wait until I see "Z68919"
    And I fill in "unladenKg" with "1"
    And I fill in "unladenLbs" with "2"
    And I fill in "ratedPowerKw" with "3"
    And I fill in "ratedPowerHp" with "4"
    Then I check "logo"
    And press "Download Name Plate"
    And I wait until I see "Success"