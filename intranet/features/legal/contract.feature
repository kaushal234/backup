@contract
Feature: Directory / Contract Type

  Scenario: As an anonymous user, test that I'm not allowed to see contract type pages
    When I go to "/legal/contracts"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that I'm allowed to see contract page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/legal/contracts"
    Then the response status code should be 200
    And I should see an "a[href$='/legal/contracts/ai-analyze']" element
    And I should see "Add"

  @javascript
  Scenario: A basic user can can add a contract type
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/legal/contracts/add"
    And I should be on the exact url "/legal/contracts/add"
    And I wait until I see "Add Contract"
    And I fill in "shortDescription" with "My Short Description"
    Then I fill in dropdown "category" with "BANK"
    Then I fill in dropdown "subCategory" with "Permanent without ending"
    And I fill in "description" with "My Description"
    And I fill in "jurisdiction" with "My Jurisdiction"
    Then I fill in date picker "startDate" with "today"
    Then I fill in dropdown "divisions" with "By Zero"
    Then I fill in dropdown "regions" with "TLD by zero"
    Then I fill in dropdown "businessUnits" with "Ritchie Group"
    Then I fill in dropdown "premises" with "Andalouza"
    And I fill in "value" with "1"
    Then I fill in dropdown "currency" with "CAD"
    Then I fill in "observationValue" with "Winter is coming"
    Then I fill in date picker "expirationDate" with "today"
    And I fill in "renewalPeriod" with "1"
    Then I fill in dropdown "renewalUnit" with "Day"
    Then I check "indefinitePeriodType"
    Then I check "automaticRenewal"
    Then I fill in "observationTerm" with "Winter is coming hard"
    And I fill in "externalParty" with "External Party"
    Then I click on element ID "addInternalPartyButton"
    And I fill in "internalParty[0]" with "Internal Party 1"
    Then I click on element ID "addOtherPartySignatoriesButton"
    And I fill in "otherPartySignatories[0]" with "Other Party Signatories 1"
    And press "Submit"
    And I wait until I see "Saved"
    
  @javascript  
  Scenario: As owner, test that I can edit a contract
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/legal/contracts/1/edit"
    And I wait until I see "Edit Contract"
    And I fill in "shortDescription" with "My Updated Short Description"
    And press "Submit"
    And I wait until I see "Saved"

  @javascript
  Scenario: As owner, test that I can edit a contract status
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/legal/contracts/1/status"
    And I wait until I see "Edit Contract Status"
    And I fill in dropdown "status" with "Expired"
    Then I fill in rich textarea "observationStatus" with "ça va couper chérie"
    And press "Submit"