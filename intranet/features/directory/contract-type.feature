Feature: Directory / Contract Type

  Scenario: As an anonymous user, test that I'm not allowed to see contract type pages
    When I go to "/directory/contract-types"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that I'm allowed to see contract types pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/contract-types"
    Then the response status code should be 200
    And The module should be "ESM"
    And I should not see an "a[href$='/directory/contract-types/1/edit']" element
    And I should not see "Add contract type"

  Scenario: As superuser, test that I'm allowed to see contract types pages
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/contract-types"
    Then the response status code should be 200
    And I should see an "a[href$='/directory/contract-types/1/edit']" element
    And I should see "Add contract type"

  Scenario: As a superuser, test that I can add a contract type
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/contract-types"
    Then the response status code should be 200
    And I fill in "contract_type[name]" with "Contrat à vie"
    And I fill in "contract_type[description]" with "Description  pour le test"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/contract-types"
    And I should see "Contrat à vie"

  Scenario: As a basic user, test that I can't edit a contract type
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/contract-types/1/edit"
    Then the response status code should be 200
    And I should see "You do not have permissions"

  Scenario: As a superuser, test that I can edit a contract type
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/contract-types/1/edit"
    Then the response status code should be 200
    And I fill in "contract_type[name]" with "Interim"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/directory/contract-types"
    And I should see "Interim"

  Scenario: As a superuser, test that I can delete a contract type
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/contract-types/3/delete"
    Then the response status code should be 200
    And I should be on "/directory/contract-types"
    And I should see "Contract type deleted successfully"
