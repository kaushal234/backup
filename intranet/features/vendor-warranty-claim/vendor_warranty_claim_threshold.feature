Feature: Purchasing / VWC

  Scenario: As a basic user, test that i'm allowed to see VWC pages detail
    Given I authenticate as "user-qam@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims/thresholds"
    Then the response status code should be 200
    And The module should be "VWC"
    And I should see "VWC Thresholds"

  @authentication
  Scenario: As a superuser, test that i can add a VWC threshold
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims/thresholds"
    Then the response status code should be 200
    And I fill in the following:
      | newThresholdForm[location]   |          /locations/26 |
      | newThresholdForm[threshold]  |                  10011 |
    And I press "newThresholdForm[submit]"
    Then the response status code should be 200
    And I should be on "/purchasing/vendor-warranty-claims/thresholds"
    And I should see "Thresholds have been successfully updated"
    And I should see "location_group"

  @authentication
  Scenario: As a superuser, test that i can t add a VWC threshold on a location already used
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims/thresholds"
    Then the response status code should be 200
    And I fill in the following:
      | newThresholdForm[location]   |          /locations/26 |
      | newThresholdForm[threshold]  |                  10011 |
    And I press "newThresholdForm[submit]"
    Then the response status code should be 200
    And I should be on "/purchasing/vendor-warranty-claims/thresholds"
    And I should see "An error occurred location: This value is already used."

  @authentication
  Scenario: As a superuser, test that i can edit a VWC threshold
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims/thresholds"
    Then the response status code should be 200
    And I fill in the following:
      | editThresholdForms[thresholdForms][2][threshold]   |          128 |
    And I press "editThresholdForms[thresholdForms][2][submit]"
    Then the response status code should be 200
    And I should be on "/purchasing/vendor-warranty-claims/thresholds"
    And I should see an "input[value$='128']" element

  Scenario: As a user-basic, test that i can t add a VWC threshold
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims/thresholds"
    Then the response status code should be 200
    And I fill in the following:
      | newThresholdForm[location]   |          /locations/10 |
      | newThresholdForm[threshold]  |                  10011 |
    And I press "newThresholdForm[submit]"
    Then the response status code should be 200
    And I should be on "/purchasing/vendor-warranty-claims/thresholds"
    And I should see "An error occurred"
    And I should see "This action is restricted to QAMs of the associated location."

  Scenario: As a user-qam, test that i can t add a VWC threshold on a location where i am not the qam
    Given I authenticate as "user-qam@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims/thresholds"
    Then the response status code should be 200
    And I fill in the following:
      | newThresholdForm[location]   |           /locations/1 |
      | newThresholdForm[threshold]  |                  10011 |
    And I press "newThresholdForm[submit]"
    Then the response status code should be 200
    And I should be on "/purchasing/vendor-warranty-claims/thresholds"
    And I should see "An error occurred"
    And I should see "This action is restricted to QAMs of the associated location."

  @authentication
  Scenario: As a user-qam@tld.fr, test that i can t edit a VWC threshold on a location where i am not the qam
    Given I authenticate as "user-qam@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims/thresholds"
    Then the response status code should be 200
    And I fill in the following:
      | editThresholdForms[thresholdForms][2][threshold]   |  2650 |
    And I press "editThresholdForms[thresholdForms][2][submit]"
    Then the response status code should be 200
    And I should be on "/purchasing/vendor-warranty-claims/thresholds"
    And I should see "An error occurred"
    And I should see "This action is restricted to QAMs of the associated location."

  @authentication
  Scenario: As a user-qam, test that i can add a VWC threshold on a location where i am the qam
    Given I authenticate as "user-qam@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims/thresholds"
    Then the response status code should be 200
    And I fill in the following:
      | newThresholdForm[location]   |          /locations/29 |
      | newThresholdForm[threshold]  |                  50002 |
    And I press "newThresholdForm[submit]"
    Then the response status code should be 200
    And I should be on "/purchasing/vendor-warranty-claims/thresholds"
    And I should see "Thresholds have been successfully updated"

  Scenario: As a user-qam@tld.fr, test that i can edit a VWC threshold on a location where i am the qam
    Given I authenticate as "user-qam@tld.fr" with "P@ssw0rd15chars"
    When I go to "/purchasing/vendor-warranty-claims/thresholds"
    Then the response status code should be 200
    And I fill in the following:
      | editThresholdForms[thresholdForms][3][threshold]   |  999 |
    And I press "editThresholdForms[thresholdForms][3][submit]"
    Then the response status code should be 200
    And I should be on "/purchasing/vendor-warranty-claims/thresholds"
    And I should see "Thresholds have been successfully updated"
    And I should see an "input[value$='999']" element
